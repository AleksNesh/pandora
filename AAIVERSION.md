### v0.5.0

+ Sync develop and master branches for prep release

### v0.5.1

+ Update production database config files to use internal IP address

### v0.6.0

+ Upgrades WordPress to 3.9 and adds WordPress Importer plugin for migrating content.

### v0.6.1

+ Upgrades Fishpig_Wordpress module

### v0.6.2

+ OOPS...missed some Fishpig_Wordpress directories/files

### v0.6.3

+ See this [v0.6.3 tree](https://github.com/pandoramoa/pandoramoa.com/tree/v0.6.3)

### v0.6.4

+ Minor styling updates/changes

### v0.6.5

+ Latest styling changes

### v0.6.6

+ See this [v0.6.6 tree](https://github.com/pandoramoa/pandoramoa.com/tree/v0.6.6)

### v0.6.7

+ Adds CMJ Custom Stock Status module for better control of availability message on product pages
+ Upgrades Ebizmarts MageMonkey from 1.1.19 to 1.1.21

### v0.6.8

+ Fixes issues with configurable products/options and several styling issues.

### v0.6.10

+ Removes duplicate jQuery from EM_Megamenupro in favor of Ash_Jquery versions

### v0.6.11

+ Styling fixes.
+ Remove 'To' and 'From' fields from Gift Messages

### v0.6.12

+ Adds Amasty_Number module for admins to set custom starting order numbers and/or prefixes

### v0.6.13

+ Fixes issue with merging/compacting JS files that was causing issues with configurable/custom options to not be displayed.

### v0.6.14

+ Fixes issue with Gift Cards payment option not showing up during checkout

### v0.6.15

+ Fixes issues with missing Gift Cards in cart and checkout pages
+ Adds a module to allow customer's to add a comment to an order
+ Adds Fishpig_AttributeSplashPage module
+ Upgrades Fishpig_Wordpress to version 3.1.0.15


### v0.7.0

+ RELEASE - sync develop/master branches

### v0.7.1

+ HOTFIX
  + FIXES name of repository (moved from augustash ot pandoramoa)
  + FIXES setting permissions on wordpress nested shared directories (cache, uploads)

### v0.7.2

+ Fixes fetching of system config value for unsecure base_url so it can be used in setting license numbers in each environment


### v0.7.3

RELEASES

+ Ability to partially invoice orders and capture payments
+ Minor bug squashing

### v0.7.4

+ FIXES issues with the TinyBrick_Authorizenetcim module and handling of authorization and capture methods on guest orders that used the AIM methods instead of the CIM methods

### v0.8.0

Sync latest development code into production

Major areas are:

+ Improved UPS Shipping Labels module
+ Improved True Order Edit module
+ Improved Authorize.net CIM module (allows for partial authorizations/capturing with multiple invoices)
+ Various improvements and bug fixes

### v0.9.0

+ Adds integration between Xtento_GridActions module and Infomodus_Upslabel (through custom Pan_Gridactions and Pan_Infomodusupslabel modules)
+ Re-adds missing "Create UPS label" checkbox to admin page for creating shipments (caused by customizations for Pan_OrderEdit module templates)
+ Quiet down some logging from Pan_Authorizenetcim

### v0.9.1

+ Minor fix for syntax error in Capfile

### v0.9.2

+ Fixes issue with Infomodus_Upslabel_Helper_Help escaping of XML if the PHP mbstring extension is not installed
+ Fixes issue with calling of Zend_Pdf class (hint: not Zend_PDF)

### v0.9.3

+ Sync production files back down to the repo since some changes were made on the server
+ Update Fishpig_Wordpress module to 3.1.1.15
+ Update WordPress themes and plugins (still version 3.9.1)

### v0.9.4

+ (HOTFIX) Fixes issue with filtering Giftcards (overrides some Webtex_Giftcards functionality)

### v0.9.5

+ (HOTFIX) Adds Ash_Phonemask module to format phone/fax numbers prior to saving to the database

### v0.9.6

+ (HOTFIX) Fixes issue with escaping XML when the PHP mbstring extension isn't installed

### v0.9.7

+ (HOTFIX) Fix deployments to account for NFS mounted directories from DB01 to APP01 and APP02

### v0.9.8

+ (HOTFIX) Add 'content' shared directory

### v0.9.9

+ (HOTFIX) Add Google Tracking code to Checkout Success page & add CSS style for top banner placement

### v1.0.0

RELEASES

+ Install Magestore Giftwrap 3.0 to add packaging functionality
+ Install Amasty Extend Order Grid 1.7.4 to add ability to select shipping option on the main Order Grid
+ Modify Packing Slip template to match layout for Pandora invoice paper

### v1.0.1

+ (HOTFIX) Display Gift Cards used on Order in Admin Order View
+ (HOTFIX) Modify the Out of Stock CSS style from red to purple
+ (HOTFIX) Automatically add the Order Number to the UPS label Reference Number area to display on printed PDF label
+ (HOTFIX) Remove HTTP protocol from Google Fonts link to work behind https and http
+ (HOTFIX) Add remote address headers to config to allow CloudFlare to see real client IP if behind reverse proxy
+ (HOTFIX) Format bottom of invoice and packing slip templates

### v1.0.2

+ (HOTFIX) Fixes issue with deployments and `magento:purge_cache` task unable to remove files owned by `memcached` user. Had to add `/bin/chown` to the DEPLOYMENT sudoers alias on the servers.

### v1.0.3

+ RELEASE - Syncing some commits from Kevin's hotfixes into the master branch.

### v1.0.4

+ (HOTFIX) Fix issue w/ potential JS error if phone/fax fields not present
+ (HOTFIX) Revert Carl's changes from commit [2546258](https://github.com/pandoramoa/pandoramoa.com/tree/254625809dff7ad8549021b52d5dc14ad8afff10) because it was causing some mis-alignment issues with slide titles and some slides were missing the white triangle background all together
+ (HOTFIX) Adds Pan_Megamenupro to patch EM_Megamenupro module and potentially devastating bug of losing the complete menu once it reaches a certain number of items in the menu. Also removes duplicate jQuery inclusion.

### v1.0.5

+ (HOTFIX) Add Bing conversion tracking to the Google Tracking section on the final checkout page
+ (HOTFIX) Update Amasty Free Gift extension from 1.0.8 to 1.0.9
+ (HOTFIX) Install Wfs Disable Emails 1.0.1 extension to control Magento default emails being sent from system

### v1.0.6

+ (HOTFIX) Order Comment extension modification by Amasty for Order Grid issue
+ (HOTFIX) Add Table CSS style for Shipping Chart
+ (HOTFIX) Uninstall / Remove TinyBrick Order Archive extension - fix Order Grid loading issue
+ (HOTFIX) Uninstall Wfs Disable Email extension - function disables all emails instead of what is chosen
+ (HOTFIX) Install WebShopApps Matrix Shipping extension to replace Amasty Shipping Table Rates extension (fix configurable issues)
+ (HOTFIX) Replace old Google Analytics code with new Google Universal Analytics code
+ (HOTFIX) CSS changes by AAI for compilation errors

### v1.0.7

+ (HOTFIX) Use `nfs_shared_path` variable for `magento:purge_cache` task instead of `shared_path` during deployments

### v1.0.8

+ (HOTFIX) Infomodus UPS Shipping Label fix - Quantum View Notifications enabled automatically at UPS Label creation
+ (HOTFIX) Remove Add to MailChimp from Mass Action menu in Ebizmarts MageMonkey Observer
+ (HOTFIX) Remove Create UPS Labels for Orders from Mass Action menu in PAN Infomodus UPS Observer
+ (HOTFIX) Add Card Reference input box to the Add Gift Card form to allow a reference ID to be added with offline gift cards
+ (HOTFIX) Add Universal Analytics E-commerce tracking code for transactional details in Google
+ (HOTFIX) Add image size into header code for logo in PAN template
+ (HOTFIX) Add fixed height and width to Ash Slideshow code for images
+ (HOTFIX) Install DirectShop Fraud Extension v.1 to integrate with MaxMind fraud protection
+ (HOTFIX) Install Aromicon Google Universal Analytics 1.2.0 to integrate Universal Analytics code and conversion tracking
+ (HOTFIX) Install Anaraky GDRT 1.0.9 to integrate Google Remarketing code into site
+ (HOTFIX) Modify contact form to hard-code URL instead of missing PHP tag


### v1.1.0

+ (RELEASE) Initial release of Build a Bracelet (aka, 'Pan_JewelryDesigner')
+ Re-installed Magestore_Giftwrap module b/c it was having issues of causing a fatal error on staging (and still is), so the Capfile has been modified to add this problematic module to the list of modules that are disabled upon deployments until we can focus on debugging the issue with it.

### v1.1.1

+ (HOTFIX) Fixes issues with Magestore_Giftwrap and mis-named filenames

### v1.1.2

+ (HOTFIX) Disables Magestore_Giftwrap on deploys until it can be debugged as to how it messes up add to cart and checkout functionality


### v1.1.3

+ Add ability to mark BAB items (bracelets and charms) as already owned & exclude from wishlist/cart
+ Add AddThis sharing capability w/ public interface to allow anonymous users to view a customer's shared bracelet
+ Fix an issue w/ IE and links that contained an extra 'false' in the href

### v1.1.4

+ Add ability to mark BAB clips as already owned & exclude from wishlist/cart
+ Fix trash can issues
+ Add IE 8 and below warning message
+ Don't allow guests to edit/clone Inspiration Bracelets
+ Improve some flash message content with links to login or register pages

### v1.1.5

+ Fix issue w/ loading/reloading bracelets and keeping track of 'bracelet_has_clip_spots' configuration value through bracelet life cycle

### v1.1.6

+ FOR REAL FIXED the loading of a design from the My Bracelets area without causing duplicate beads or missing bracelet image
+ Add instructions to interface
+ Add server side caching of collection data (you can clear it through Magento's collection data cache or by flushing the cache)
+ Remove infinite scrolling in sidebar products grid and instead use the cached data
+ Add save button for logged in users
+ Save design when the design name changes

### v1.1.7

+ (HOTFIX) Disable Alpine_PrintPdf.xml extension by default until full version is debugged
+ (HOTFIX) Modify CustomStockStatus catalog grid view to display On Backorder instead of Out of Stock
+ (HOTFIX) Add custom CMS Block to above the Shopping Cart to display packaging note & allow for holiday messages
+ (HOTFIX) Add PAN billing.phtml and shipping.phtml templates to customize / clarify wording for adding a new address

### v1.1.8

+ Fix issue w/ admin area pulling in non-inspirational bracelets

### v1.1.9

+ Fix issue with bracelet super_attributes being carried through when design is reloaded

### v1.1.10

+ RELEASE some recent features (mobile menus, Kevin's hotfix/1.1.7 branch)

### v1.1.11

+ Add BAB link to top links

### v1.1.12

+ (HOTFIX) Change BAB link to have frontend users jump immediately into builder interface

### v1.1.13

+ (HOTFIX) Fix some styling issues for admin area BAB
+ (HOTFIX) Fix permissions configuration issue for admin roles

### v1.1.14

+ RELEASE some fixes for mobile menu

### v1.1.15

+ (HOTFIX) Fixes issue with allowing admin users access to designer app

### v1.1.16

+ (HOTFIX) Fixes issue with sorting by price (cast strings to floats)

### v1.1.17

+ (HOTFIX) DirectShop MaxMind Fraud file fix - change getRawBody to getBody
+ (HOTFIX) Add Mageworx Tweaks v1.0.7 to incorporate functional sort by Newest and Bestsellers
+ (HOTFIX) Remove Billing Page layout XML reference from Tweaks to fix Billing Page form issue
+ (HOTFIX) Upload mobile Pandora Stores landing page
+ (HOTFIX) Add isShellDisabled to Cron to fix issue with Cron jobs not running
+ (HOTFIX) Modify Xtento Order Export extension to allow for incremental orders being added to CSV file instead of running each individually
+ (HOTFIX) Add export script for manually emailing incremental CSV file of Shipped Orders

### v1.1.18

+ (HOTFIX) Fix Build a Bracelet relative link url

### v1.1.19

+ FEATURE - Add MageWorx SearchSuite Extension 1.0.0 - including AutoComplete, Solr and Sphinx - REMOVED DUE TO SERVER SOFTWARE CONFLICT
+ FEATURE - Add Mirasvit Sphinx Search Ultimate Extension

### v1.1.20

+ (HOTFIX) Add custom PAN price.phtml file for manage "Starting At" text for Grouped Products
+ (HOTFIX) Change Grouped Option for Out of Stock to On Backorder
+ (HOTFIX) Add custom PAN template for shipping_method.phtml to add shipping chart and add Static Block reference

### v1.1.21

+ (HOTFIX) Added a loading message for Build a Bracelet interfaces

### v1.1.22

+ (HOTFIX) Fix issue w/ loading message in admin area

### v1.1.23

+ (HOTFIX) Email Export dump file fixed
+ (HOTFIX) Update Build a Bracelet Instructions & styling
+ (HOTFIX) Add product thumbnail to Grouped Product page and adjust stock message
+ (HOTFIX) Lookbook JS conflict fix for adding HotSpots

### v.1.1.24

+ FEATURE - Install WebShopApps Endicia 1.6 to add Endicia label integration to Magento

### v.1.1.25

+ (HOTFIX) Add custom PAN file for coupon.phtml
+ (HOTFIX) Add Lookbook thumbnail navigation arrows

### v1.1.26

+ (HOTFIX) Have redis store zend cache in separate db
+ (HOTFIX) Adds Ash_Cacheboost to cache magento blocks

### v1.1.27

+ FEATURE - Update Mirasvit Sphinx to 2.3.2.1047
+ (HOTFIX) - Update AutoComplete CSS Style
+ (HOTFIX) - Enable Alpine Print PDF by default

### v1.1.28
+ (HOTFIX) Updated retina shopping cart icon from default
+ (HOTFIX) Changed Order Comment text on Confirmation page in checkout to read Shipping Instructions to match return instructions
+ (HOTFIX) Remove auto-enable of Searchanise extension in config >  deploy > production.rb
+ (HOTFIX) Remove the Add to Bag icon on product category grid since the rollover removes it anyway, confusing customers
+ (HOTFIX) Replace all shopping bag icons to a better / classier icon

### v1.1.29

- SKIPPED by AAI - 

### v1.1.30

+ (HOTFIX) - update bracelet placeholder and trashcan images for Build a Bracelet
+ sync files from production b/c PAN is unsure if everything is in the repo

### v1.1.31

+ (HOTFIX) - Remove the Add to Bag icon from search category grid (same reason as above in v1.1.28 for category grid)
+ (HOTFIX) - Change title of Build a Bracelet to Bracelet Designer on top nav and main page

### v1.1.32

+ (HOTFIX) - Add featured_products_slider.phtml file to PAN to allow for custom slider for new website design concept
+ (HOTFIX) - Add Ajax Cart JS insertion to Attribute Splash Page XML layout file to allow adding to cart on those pages
+ (HOTFIX) - Turn off Snap Card / Pandora Gift Card module due to error page on Admin Order View screen
+ (HOTFIX) - Add Alert Box CSS style to local.css file to add ability to create alert boxes on pages
+ (HOTFIX) - Add warning message on shipping.phtml checkout page to alert shipping to only US addresses
+ (HOTFIX) - Update Pandora logo to remove Unforgettable Moments per new guidelines

### v1.1.33

+ (HOTFIX) - Add warning message on cc.phtml file for authorize.net CIM extension to alert to US issued cards only
+ (HOTFIX) - Change label for Build a Bracelet to Bracelet Builder in pan_jewelrydesigner.xml

### v1.1.34

+ (HOTFIX) - Remove APP03 from production.rb file for deployments as APP03 is no longer in service

### v1.1.35

+ (HOTFIX) - Disable MageWorx Tweaks extension to allow use of fixed Amasty Sorting for Best Sellers
+ (HOTFIX) - Comment out all MageWorx Tweaks events in app/code/local/MageWorx/Tweaks/etc/config.xml

### v1.1.36

+ (UPDATE) - Update FishPig WordPress extension to 3.1.1.26 version

### v1.1.37

+ (HOTFIX) - Change Out of Stock to On Backorder for catalog listing
+ (HOTFIX) - Replace Shopping Bag icons with PANDORA icon bag images

### v1.1.38

+ (UPDATE) - Install PAN18 - Alpine CMS update files for new design concept

### v1.1.39

+ (HOTFIX) - Update new Homepage CSS style
+ (HOTFIX) - Change background image for home to reflect wording & opacity fix
+ (HOTFIX) - Adjust Disney banner background image
+ (HOTFIX) - Fix .item conflict with quick Cart display
+ (HOTFIX) - Change opacity for rollover images
+ (HOTFIX) - Update Bing Tracking code with new tracking

### v1.1.40

+ (UPDATE) - Comment out conflict on PDF Customizer script
+ (UPDATE) - Remove Kevin's email from the export email notifications
+ (UPDATE) - Remove Cart icon from configurable product display on Splash Pages
+ (UPDATE) - Correct Disney banner background color
+ (UPDATE) - Fix CMS class issue on new homepage
+ (UPDATE) - Update alert CSS style to change to different style
+ (UPDATE) - Update alert boxes during checkout with new CSS callouts (remove yellow)
+ (UPDATE) - Add stock status column head for Shipping USPS
+ (UPDATE) - Change default weight for Endicia shipping integration to 1 and type to OZ
+ (UPDATE) - Change the font selection for H2 and H3 on the Blog

### v1.1.41

+ (HOTFIX) - Remove old PANDORA logos and replace with PANDORA Store logo
+ (HOTFIX) - Replace Disney homepage image to add in missing copyright for Disney
+ (HOTFIX) - Remove Mirasvit highlight yellow from forms

### v1.1.42

+ (HOTFIX) - Adjust API Controller for Bracelet Builder to correct category names, fixing spinning wheel when loading
+ (HOTFIX) - Apply SUPEE-6285 patch for Magento

### v1.1.43

+ (HOTFIX) - Update WebShopApps Matrixrate to v5.1 to fix issue after updating patch SUPEE-6285

### v1.1.44

+ (HOTFIX) - Replace login_bg.jpg image for JewelryDesigner and main login
+ (HOTFIX) - Update Xtento GridActions fix for the SUPEE patch
+ (HOTFIX) - Remove 30px top padding from local and override css files to fix homepage gap under nav
+ (HOTFIX) - Add retina logo for website (logo@2x.png)

### v1.1.45

+ (HOTFIX) - Comment out Related Blog Posts on product view until we add to Tab instead (layout/wordpress.xml)
+ (HOTFIX) - Remove Cart2Cart bridge folder
+ (HOTFIX) - Remove Pandora from Related Products text since it is in cursive and cannot be all caps
+ (HOTFIX) - Change the Disney homepage H3 font to variations of Copperplate
+ (HOTFIX) - Merge in Authorize.net CIM fix for Admin Orders to choose new or saved credit card (by Speroteck)

### v1.1.46

+ (HOTFIX) - Apply SUPEE-6482 patch for Magento

### v1.1.47

+ (UPDATE) - Updated the RocketWeb Google Base Feed Generator extension to version 1.6.2

### v1.1.48

+ (FEATURE) - Install Yireo New Relic extension to add data to New Relic reporting

### v1.1.49

+ (UPDATE) - Update Mirasvit Sphinx Search Ultimate extension to version 2.3.2.1219
+ (HOTFIX) - Update Disney homepage image to reflect Pandora approved version

### v1.1.50

+ (HOTFIX) - Add EDIT link to modify saved credit cards
+ (HOTFIX) - Force HTTPS for editing credit cards in account

### v1.1.51

+ (HOTFIX) - Merge Authorize.net CIM e00039 fix from Speroteck, searching for profile before trying to create
+ (UPDATE) - Update Mirasvit Sphinx Search Ultimate extension to version 2.3.2.1230

### v1.1.52

+ (HOTFIX) - Hide loader icon by default on search box & add missing loader.gif file
+ (HOTFIX) - Save storeID as 1 instead of 0 for saved credit cards
+ (HOTFIX) - Apply online fix for getAllisdSql error found in New Relic (fix for Enterprise, fixed in Community v1.9)
+ (HOTFIX) - Restore content.phtml file within the Adminhtml template, missing from hotfix 1.1.51 Mirasvit update, causing CMS content box to be missing

### v1.1.53

+ (UPDATE) - Update FishPig eBizmarts Mage Monkey to v.1.1.30