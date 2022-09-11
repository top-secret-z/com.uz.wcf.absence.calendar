<?php
namespace calendar\system\cronjob;
use calendar\data\event\Event;
use calendar\data\event\EventAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Cronjob for Absence calendar events
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.absence.calendar
 */
class AbsenceCalendarCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// only if configured
		if (!MODULE_ABSENCE) return;
		
		// find and delete absence events without absence; limit to 100
		$eventIDs = [];
		$sql = "SELECT		eventID
				FROM		calendar".WCF_N."_event event_table
				LEFT JOIN	wcf".WCF_N."_user user_table
						ON (user_table.userID = event_table.absentUserID)
				WHERE	event_table.absentUserID IS NOT NULL AND user_table.absentFrom = 0";
		$statement = WCF::getDB()->prepareStatement($sql, 100);
		$statement->execute();
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
	}
}
