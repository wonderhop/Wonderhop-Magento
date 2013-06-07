/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * Merchandising Made Easy
 *
 * @package Eyemagine_Merchandising
 * @author EYEMAGINE <support@eyemaginetech.com>
 * @category Eyemagine
 * @copyright Copyright (c) 2013 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 */

-------------------------------------------------------------------------------
DESCRIPTION:
-------------------------------------------------------------------------------

Merchandising products can be difficult and time-consuming when you have to manage lots of products. 
Merchandising Made Easy is a simple Magento enhancement that gives you, the store owner, the ability 
to easily drag and drop products into the most profitable position on your Magento store.

Module Files:

  - app/code/community/Eyemagine/Merchandising/etc/config.xml
  - app/code/community/Eyemagine/Merchandising/*
  - app/design/adminhtml/default/eyemagine/layout/local.xml
  - app/design/adminhtml/default/eyemagine/template/catalog/*
  - app/design/adminhtml/default/eyemagine/template/widget/*
  - app/etc/modules/Eyemagine_Merchandising.xml
  - skin/adminhtml/default/default/eyemagine/* 


-------------------------------------------------------------------------------
COMPATIBILITY:
-------------------------------------------------------------------------------
  * Magento Community Edition: 1.4 - 1.7


-------------------------------------------------------------------------------
ATTENTION:
-------------------------------------------------------------------------------
  * Currently, code is modifying the Category Products tab.  Position are disabled 
  to prevent motifications done by MME custom list sort order.

-------------------------------------------------------------------------------
RELEASE NOTES:
-------------------------------------------------------------------------------
==== 1.0.5.2 ====
Released at 3/19/2013
	- Removed instant ajax saving everytime a product is sorted to the top by clicking
    the move to top button or by drag and drop. A more efficient strategy was implemented
    to let the user sort products first and then save the sorted list by a single click 
    process.

==== 1.0.5.1 ====
Released at 11/26/2012
    - Filter catalog invisible products

==== 1.0.5.0 ====
    - Released at 8/15/2012

=== Fixes ===
    - Fixed missing bottom in top float bar
    - Fixed drag and drop position in js
    - Fixed Catalog Bug
    - Fixed Layout, removed main.xml and use local.xml

=== Improvements ===
    - Update to be compatible with Magento CE 1.6 and EE 1.11
    - Resize image icon to 75X75

=== Changes ===
    - Move code pool from local to community


==== 1.0.0.0 ====
Known issues:
  * not working properly in IE 9, due to the defect of prototype 1.6 in IE 9.