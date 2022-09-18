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
namespace calendar\system\cronjob;

use calendar\data\event\Event;
use calendar\data\event\EventAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Cronjob for Absence calendar events
 */
class AbsenceCalendarCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // only if configured
        if (!MODULE_ABSENCE) {
            return;
        }

        // find and delete absence events without absence; limit to 100
        $eventIDs = [];
        $sql = "SELECT        eventID
                FROM        calendar" . WCF_N . "_event event_table
                LEFT JOIN    wcf" . WCF_N . "_user user_table
                        ON (user_table.userID = event_table.absentUserID)
                WHERE    event_table.absentUserID IS NOT NULL AND user_table.absentFrom = 0";
        $statement = WCF::getDB()->prepareStatement($sql, 100);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $eventIDs[] = $row['eventID'];
        }
        if (!empty($eventIDs)) {
            foreach ($eventIDs as $id) {
                $event = new Event($id);
                if ($event->eventID) {
                    $action = new EventAction([$event], 'delete');
                    $action->executeAction();
                }
            }
        }
    }
}
