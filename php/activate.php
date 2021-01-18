<?php

/**
 * WP-Broker activation routines
 *
 * @package             wp-broker
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * This file is part of wp-broker.
 *
 * wp-broker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wp-broker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wp-broker.  If not, see <https://www.gnu.org/licenses/>.
 */


 namespace WPBroker;

 class Activator {

    public function deactivate() {

    }

    public function uninstall() {
        // instantiate the Migrate model and run the activate method
        require_once(__DIR__ . '/models/base.php');
        require_once(__DIR__ . "/models/migration.php");
        $model = new Migration();
        $model->uninstall();
    }

    public function activate() {
        update_option('wpbroker_version', 'new');
        $this->update();

        $roles = wp_roles();
        if(isset($roles->role_objects['subscriber'])) {
            $roles->role_objects['subscriber']->add_cap('has_wall', true);
        }
        if (isset($roles->role_objects['administrator'])) {
            $roles->role_objects['administrator']->add_cap('has_wall', true);
        }
    }

    public function upgrade() {
        update_option('wpbroker_version', 'new');
    }

    public function update() {
        if(get_option("wpbroker_version") == "new") {
            // instantiate the Migrate model and run the activate method
            require_once(__DIR__ . '/lib/base.php');
            require_once(__DIR__ . "/lib/migration.php");
            // this loads all database migrations from file and executes
            // all those that are not yet marked as migrated
            $model = new Migration();
            $model->activate();
            update_option('wpbroker_version', strftime('%F %T'));
        }
    }
 }