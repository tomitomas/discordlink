<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function discordlink_install() {
    discordlink::CreateCmd();
    discordlink::updateobject();

    $default = json_decode('{"motion":":person_walking:","door":":door:","windows":":frame_photo:","light":":bulb:","outlet":":electric_plug:","temperature":":thermometer:","humidity":":droplet:","luminosity":":sunny:","power":":cloud_lightning:","security":":rotating_light:","shutter":":beginner:","deamon_ok":":green_circle:","deamon_nok":":red_circle:","dep_ok":":green_circle:","dep_progress":":orange_circle:","dep_nok":":red_circle:","batterie_ok":":green_circle:","batterie_progress":":orange_circle:","batterie_nok":":red_circle:","zwave_ok":":green_circle:","zwave_nok":":red_circle:"}');
    $emojyarray = config::byKey('emojy', 'discordlink', $default);
    config::save('emojy', $emojyarray, 'discordlink');
}

function discordlink_update() {
    discordlink::CreateCmd();
    discordlink::updateobject();

    $default = json_decode('{"motion":":person_walking:","door":":door:","windows":":frame_photo:","light":":bulb:","outlet":":electric_plug:","temperature":":thermometer:","humidity":":droplet:","luminosity":":sunny:","power":":cloud_lightning:","security":":rotating_light:","shutter":":beginner:","deamon_ok":":green_circle:","deamon_nok":":red_circle:","dep_ok":":green_circle:","dep_progress":":orange_circle:","dep_nok":":red_circle:","batterie_ok":":green_circle:","batterie_progress":":orange_circle:","batterie_nok":":red_circle:","zwave_ok":":green_circle:","zwave_nok":":red_circle:"}');
    $emojyarray = config::byKey('emojy', 'discordlink', $default);
    config::save('emojy', $emojyarray, 'discordlink');
}


function discordlink_remove() {
    discordlink::CreateCmd();
}

?>
