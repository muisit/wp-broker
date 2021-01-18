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

 class Display {
    public function adminPage() {
        echo <<<HEREDOC
        <div id="wpbroker-root"></div>
HEREDOC;
    }

    public function scripts($page)  {
        error_log("wpbroker: page is $page");
        if (in_array($page, array("toplevel_page_WPBroker"))) {
            error_log("adding admin.js");
            $script = plugins_url('/dist/admin.js', __DIR__);
            $this->enqueue_code($script);
            wp_enqueue_style( 'wpbroker', plugins_url('/dist/admin.css', __DIR__), array(), '1.0.0' );
        }
    }

    private function enqueue_code($script) {
        // insert a small piece of html to load the scripts
        wp_enqueue_script('wpbroker', $script, array('jquery'), '1.0.0');
        require_once(__DIR__ . '/api.php');
        $dat = new \WPBroker\API();
        $nonce = wp_create_nonce($dat->createNonceText());
        wp_localize_script(
            'wpbroker',
            'wpbroker',
            array(
                'url' => admin_url('admin-ajax.php?action=wpbroker'),
                'nonce' => $nonce,
            )
        );
    }

    public function shortCode($name, $attributes) {
        $filename = dirname(__DIR__)."/dist/$name.js";
        if(file_exists($filename)) {
            $script = plugins_url('/dist/'.$name.'.js', dirname(__DIR__));
            $this->enqueue_code($script);
            wp_enqueue_style('wpbroker', plugins_url("/dist/$name.css", dirname(__DIR__)), array(), '1.0.0');
        }
        $output = "<div id='wpbroker-$name'></div>";
        return $output;
    }    


}