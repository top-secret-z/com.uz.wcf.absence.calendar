<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace calendar\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Handles additions to absence setting form.
 */
class AbsenceSettingsFormCalendarListener implements IParameterizedEventListener
{
    /**
     * form object
     */
    protected $eventObj;

    /**
     * absentCalendar
     */
    public $absentCalendar = 0;

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $this->eventObj = $eventObj;

        $this->{$eventName}();
    }

    /**
     * Handles the readFormParameters event.
     */
    protected function readFormParameters()
    {
        $this->absentCalendar = 0;
        if (isset($_POST['absentCalendar'])) {
            $this->absentCalendar = \intval($_POST['absentCalendar']);
        }
    }

    /**
     * Handles the readParameters event.
     */
    protected function readParameters()
    {
        $this->absentCalendar = WCF::getUser()->absentCalendar;
    }

    /**
     * Handles the assignVariables event.
     */
    protected function assignVariables()
    {
        WCF::getTPL()->assign([
            'absentCalendar' => $this->absentCalendar,
        ]);
    }

    /**
     * Handles the save event.
     */
    protected function save()
    {
        // force entry if user may not choose it
        if (!ABSENCE_CALENDAR_CHOOSE) {
            $this->absentCalendar = 1;
        }
        $this->eventObj->additionalFields['absentCalendar'] = $this->absentCalendar;
    }
}
