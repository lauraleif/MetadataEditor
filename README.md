Metadata Editor (module for Omeka S)
===============================

Metadata Editor is a module for Omeka S which allows bulk editing of item metadata. This module provides functionality similar to the Omeka Classic plugin Bulk Metadata Editor.


Installation
------------

See general end user documentation for [Installing a module](http://omeka.org/s/docs/user-manual/modules/#installing-modules).

Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check your archives regularly so you can roll back if needed.

Notes
---------------

This module is adapted from the Omeka Classic plugin Bulk Metadata Editor but is not affiliated with that plugin. There are some differences in functionality, due to differences between Omeka S and Omeka Classic.

The following metadata edits are supported:
- Search and replace text (replace a search phrase with different text for all of the selected items)
- Search and replace text with PHP regular expression (no replacement happens if the regular expression is invalid)
- Prepend text to existing metadata in the selected properties 
- Append text to existing metadata in the selected properties 
- Use delimiter to separate elements into multiple properties
- Deduplicate and remove empty fields in the selected properties

Edits are only applied to the selected item sets and properties.

Troubleshooting
---------------

See online issues on the [Omeka forum] and the [module issues] page on GitHub.

License
-------

This plugin is published under [GNU/GPL v3].

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Contact
-------

Current maintainers:

* Laura Eiford

Copyright
---------

* Copyright Laura Eiford, 2019

[Omeka S]: https://omeka.org/s
[Omeka forum]: https://forum.omeka.org/c/omeka-s/modules
[GNU/GPL v3]: https://www.gnu.org/licenses/gpl-3.0.html


