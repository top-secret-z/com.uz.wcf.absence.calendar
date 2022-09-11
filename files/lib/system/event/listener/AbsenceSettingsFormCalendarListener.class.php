<?php
namespace calendar\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Handles additions to absence setting form.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.absence.calendar
 */
class AbsenceSettingsFormCalendarListener implements IParameterizedEventListener {
	/**
	 * form object
	 */
	protected $eventObj = null;
	
	/**
	 * absentCalendar
	 */
	public $absentCalendar = 0;
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$this->eventObj = $eventObj;
		
		$this->$eventName();
	}
	
	/**
	 * Handles the readFormParameters event.
	 */
	protected function readFormParameters() {
		$this->absentCalendar = 0;
		if (isset($_POST['absentCalendar'])) $this->absentCalendar = intval($_POST['absentCalendar']);
	}
	
	/**
	 * Handles the readParameters event.
	 */
	protected function readParameters() {
		$this->absentCalendar = WCF::getUser()->absentCalendar;
	}
	
	/**
	 * Handles the assignVariables event.
	 */
	protected function assignVariables() {
		WCF::getTPL()->assign([
				'absentCalendar' => $this->absentCalendar
		]);
	}
	
	/**
	 * Handles the save event.
	 */
	protected function save() {
		// force entry if user may not choose it
		if (!ABSENCE_CALENDAR_CHOOSE) $this->absentCalendar = 1;
		$this->eventObj->additionalFields['absentCalendar'] = $this->absentCalendar;
	}
}
