# PenguinPress - Manager Role
This plugin defines a manager user role that is permitted to administrate all aspects of a WordPress site with a few exceptions:

* Enabling/disabling plugins
* Changing themes
* Editing 'administrator' users

This is designed to deal with a common issue in the client services: how to give the client admin access, while still removing anything that could break the site.

## Filters

| Filter                                   | Description                                    |
| ---------------------------------------- | ---------------------------------------------- |
| `pp-manager-role/capabilities`           | The array of capabilities for the manager role |
| `pp-manager-role/admin-page-description` | The intro text on the admins listing page      |




## Must-use plugin
This plugin is written in a single file to make it easy to use as a must-use plugin without a must-use plugin loader.

## Listing administrators in the users table
Since disabling editing for individual users in the admin users listing table [isn't currently possible](https://core.trac.wordpress.org/ticket/35806#ticket), administrators are _removed_ from the list, and listed separately on an page nested in the Users admin menu. This will be changed should this core issue be addressed.
