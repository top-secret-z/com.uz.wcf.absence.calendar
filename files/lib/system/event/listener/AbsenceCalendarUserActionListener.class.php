<?php
namespace calendar\system\event\listener;
use calendar\data\event\Event;
use calendar\data\event\EventAction;
use calendar\util\CalendarUtil;
use wcf\data\user\User;
use wcf\system\category\CategoryHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\absence\AbsenceHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles additions to absence setting on user action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.absence.calendar
 */
class AbsenceCalendarUserActionListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// only action update
		if ($eventObj->getActionName() != 'update') return;
		
		// absent ?
		$params = $eventObj->getParameters();
		if (!isset($params['data']['absentFrom']) || !isset($params['data']['absentAuto'])) return;
		
		if (ABSENCE_CALENDAR_HIDEAUTO) {
			if ($params['data']['absentAuto']) return;
		}
		
		// get users
		$objects = $eventObj->getObjects();
		if (empty($objects)) return;
		
		foreach ($objects as $userEditor) {
			$user = $userEditor->getDecoratedObject();
			
			// whatever was done, delete old event first
			$eventIDs = [];
			$sql = "SELECT	eventID
					FROM	calendar".WCF_N."_event
					WHERE	absentUserID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$user->userID]);
			while ($row = $statement->fetchArray()) {
				$eventIDs[] = $row['eventID'];
			}
			if (!empty($eventIDs)) {
				foreach($eventIDs as $id) {
					$event = new Event($id);
					if ($event->eventID) {
						$action = new EventAction([$event], 'delete');
						$action->executeAction();
					}
				}
			}
			
			// need permission
			if (!ABSENCE_CALENDAR_FORCE && !WCF::getSession()->getPermission('user.calendar.canCreateEvent')) return;
			
			// honor user setting
			if (ABSENCE_CALENDAR_CHOOSE) {
				if (isset($params['data']['absentCalendar'])) {
					if (!$params['data']['absentCalendar']) return;
				}
				else {
					if (isset($user->absentCalendar) && !$user->absentCalendar) return;
				}
			}
			
			// category must exist
			$categoryIDs = explode("\n", ABSENCE_CALENDAR_CATEGORY);
			foreach ($categoryIDs as $categoryID) {
				$category = CategoryHandler::getInstance()->getCategory($categoryID);
				if ($category === null) return;
			}
			
			// absence?
			$absentFrom = $params['data']['absentFrom'];
			if (empty($absentFrom)) return;
			
			$absentTo = $params['data']['absentTo'];
			if (empty($absentTo)) return;
			
			$absentReason = $params['data']['absentReason'];
			
			$absentRep = null;
			if (isset($params['data']['absentRepID'])) {
				$rep = AbsenceHandler::getInstance()->getRep($user->userID);
				if ($rep !== null) {
					$absentRep = '<a href="' . $rep->getLink() . '">' . StringUtil::encodeHTML($rep->username) . '</a>';
				}
			}
			
			// finally...
			$language = $user->getLanguage();
			$timezoneObj = $user->getTimeZone();
			
			$subject = $language->getDynamicVariable('wcf.user.absence.calendar.subject', [
					'username' => $user->username
			]);
			
			$message = $language->getDynamicVariable('wcf.user.absence.calendar.text', [
					'reason' => empty($absentReason) ? null : $absentReason,
					'absentRep' => $absentRep
			]);
			$htmlInputProcessor = new HtmlInputProcessor();
			$htmlInputProcessor->process($message, 'com.woltlab.calendar.event', 0);
			
			$data = [
					'languageID' => $language->languageID,
					'subject' => $subject,
					'time' => TIME_NOW,
					'userID' => $user->userID,
					'username' => $user->username,
					'isDisabled' => 0,
					'categoryID' => $category->categoryID,
					'enableComments' => 0,
					'absentUserID' => $user->userID,
					'coverPhotoID' => null
			];
			
			// build event date data
			$eventDateData = [
					'startTime' => $absentFrom,
					'endTime' => $absentTo,
					'isFullDay' => 0,
					'timezone' => $timezoneObj->getName(),
					'firstDayOfWeek' => WCF::getLanguage()->get('wcf.date.firstDayOfTheWeek'),
					'repeatType' => '',
					'repeatInterval' => 1,
					'repeatWeeklyByDay' => [0],
					'repeatMonthlyByMonthDay' => 1,
					'repeatMonthlyDayOffset' => 1,
					'repeatMonthlyByWeekDay' => 1,
					'repeatYearlyByMonthDay' => 1,
					'repeatYearlyByMonth' => 1,
					'repeatYearlyDayOffset' => 1,
					'repeatYearlyByWeekDay' => 1,
					'repeatEndType' => 'unlimited',
					'repeatEndCount' => 1000,
					'repeatEndDate' => CalendarUtil::getMaxRepeatEndDate()
			];
			
			$eventData = [
					'data' => $data,
					'attachmentHandler' => null,
					'eventDateData' => $eventDateData,
					'htmlInputProcessor' => $htmlInputProcessor
			];
			if (MODULE_TAGGING) {
				$eventData['tags'] = [];
			}
			
			$objectAction = new EventAction([], 'create', $eventData);
			$objectAction->executeAction();
		}
	}
}
