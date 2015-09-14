<?php
// +-----------------------------------------------------------------------+
// | dcCKEditorAddons - a plugin for Dotclear                              |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2015 Nicolas Roudaire             http://www.nikrou.net  |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License version 2 as     |
// | published by the Free Software Foundation                             |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,            |
// | MA 02110-1301 USA.                                                    |
// +-----------------------------------------------------------------------+

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		    "dcCKEditorAddons",
	/* Description*/	"Add CKEditor plugins easily to your blog",
	/* Author */		"Nicolas Roudaire",
	/* Version */		'0.3.2',
    array('permissions' => 'contentadmin,pages',
          'type' => 'plugin',
          'dc_min' => '2.7',
    )
);
