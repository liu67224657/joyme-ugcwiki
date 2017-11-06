<?php
/**
 * ChangeSessionSkin MediaWiki Extention
 * Based on extension PersistUseskin [http://www.mediawiki.org/wiki/Extension:PersistUseskin]
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License foe more details.
 *
 * If you did not receive a copy of the GNU General Public License along with
 * this program, see <http: *www.gnu.org/licenses/>.
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 * If the "incoming" request URL contains the "&useskin=" parameter (when using pretty URLs, this
 * can also be "?useskin="), then this extension subsequently adds that parameter to all internal
 * URLs that MW creates in the HTML rendering process. Thus, clicking on a link containing the
 * '&useskin=' query string, this change of skins will be persistent during the session (if not
 * changed again) but it will not be written to the user's preferences, nor be written to the data-
 * base at all. Hence, this function is available to anonymous users, too.
 *
 * If you simply remove '&useskin=' from any one URL clicked or entered, the user goes back to the
 * default skin (if anonymous) or to his/her preference skin.
 *
 * Wikitext for a simple skin changer:
 * [{{SERVER}}{{SCRIPTPATH}}/index.php?title={{PAGENAME}}&useskin=monobook MonoBook]
 * to allow users to persistently browse the wiki in the chosen skin. Note that this extension
 * can handle standard and "pretty" URLs but that this specific example is not certain to work
 * in installations with "pretty URLs" because, if no other URL argument is present, then
 * "?useskin=monobook" has to be used but that can't be known nor checked in wikitext. However,
 * if the link is produced in the .php of a skin (rather than in an article), the code can react
 * to this situation.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This file is part of MediaWiki, it is not a valid entry point.\n";
    exit( 1 );
}

$wgExtensionCredits['parserhook'][] = array(
    'name' => 'Change Session Skin',
    'author' => 'http://www.mediawiki.org/wiki/User:Smd<br>http://www.mediawiki.org/wiki/User:BRFR',
    'url' => 'http://www.mediawiki.org/wiki/Extension:ChangeSessionSkin',
    'description' => "Persists use of the skin given in ''useskin='', if found in query string",
    'version'=>'0.26',
    'license-name' => 'GPL-2.0+',
);

$wgHooks['GetLocalURL'][] = 'ChangeSessionSkin';

function ChangeSessionSkin($title, &$url, $query)
{
    # If we find the 'useskin' query string - simply append it again to every internal link.
    # '$url' is an [[internal link]] after being rendered into HTML, at the time when this hook is called.

    global $wgRequest;
    $u = $wgRequest->getText( 'useskin' );	 // using $_GET["useskin"] had unwanted side effects

    if ( $u ) {
        if ( $u != "" ) {
            # if there is already a "?" in the URL (like in "?title="),
            # then append "&useskin=", else "?useskin="
            if ( strpos( $url, '?' ) === false) {
                $url .= "?useskin=" . $u;
            } else {
                $url .= "&useskin=" . $u;
            }
        }
    }

    # that's it ! :) go back ...
    return true;
}