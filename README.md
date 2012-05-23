# CustomPostAssist

(c) 2012 Jake Teton-Landis

## Making custom post types in Wordpress without spaghetti

Normally in Wordpress, creating a custom post type requires a slew of functions
and callbacks registered with `add_action('herp', 'callback_spaghetti_name')`.
And that's just for registering new post types. If you actually want custom data
with input fields on your new custom post type, it takes a second set of registration
functions, as well as your own input draw & save code.

My goal here is to abstract this sort of thing away from the Wordpress plugin or theme
author.

Enclosed in this project are two main classes:

 *  *JTL_CustomPostType* handles the creation and registration with Wordpress of
    actual custom post types, and also handles saving data from form fields in a
    standardized, protected way.

 *  *JTL_Field* and its subclasses represent individual inputs/fields on a custom
    post type admin page. Fields draw and save themselves (with CustomPostType's help)

    They are clumsy for now, and need significant work to be "good"

## Copyright

Copyright (C) 2012 [Jake Teton-Landis](http://jake.teton-landis.org/)
(<just.1.jake@gmail.com>)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

