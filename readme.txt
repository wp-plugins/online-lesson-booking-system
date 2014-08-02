=== Online Lesson Booking ===
Contributors: tnomi
Donate link: 
Tags: booking, reservation, appointment, timetable, lesson 
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 0.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plug-in supplies the reservation-form and scheduler for the one-to-one online lesson. 

== Description ==

Online Lesson Booking system (OLB) was made in order to equip a web site with the reservation-form and scheduler for one-to-one online lesson.

Teacher (author) sets up a timetable using a scheduler, and member (subscriber) makes a reservation by clicking timetable.
Teacher and a member are informed by e-mail in the case of reservation and cancellation. 

== Installation ==

Visit [Setup Guide (ja)](http://olbsys.com/setup/)/[(en)](http://olbsys.com/en/setup/).

= Installation =

1. Donwload plugin file and unzip it.
2. Upload "online-lesson-booking-system" folder to the "/wp-content/plugins/" directory
3. Activate the plugin through the "Plugins" menu in WordPress

= Plugin set up =

1. Open the WordPress admin panel, and go to the plugin option page "OLBsystem".
2. Menu "OLBsystem > General" is setup about reservation and a timetable.
3. Menu "OLBsystem > Special pages" is setup of the name (slug) of a page indispensable to a system.
4. Menu "OLBsystem > Mail" is Edit of the text of notice mail.
5. This plug-in uses JQuery. Insert in your theme-file(functions.php) the code "wp_enqueue_script('jquery');". 
6. Some special pages are already created, when the plug-in was activated.
7. Activate added widget "Members only", "Teachers only", and "Admins only".

= Edit the schedule of teacher = 
1. Add some users as teacher. Teacher's role is  "author".
2. Open the their profile-edit-page, check the item of "teacher".
3. Log in as a teacher. Access the "editschedule" page and set a schedule.
4. Make the information of each teacher as "post" (ex. with "teacher" category, etc.), and insert short cord [olb_weekly_schedule id="xx"].

* "id" is ID number of each teacher. ID number is confirmed with a list of users in admin-page.

= Member registration =

1. Check the item of the "membership" (anyone can register) in the admin page of WordPress.
2. A "new user's default role" is "subscriber". 
3. Members perform new user's registration themselves. Member must set item "Skype ID".
4. Administrator update the item "term of validity" of member's profile. (ex. after checking the payment from a member, etc.)

* "Ticket system" can be chosen from version 0.4.0. 

== Frequently Asked Questions ==

Visit [The User's Guide (ja)](http://olbsys.com)/[(en)](http://olbsys.com/en/) which covered all of features of this plugin.

= How is reservation information saved? =

An original table is created in a database, and it saves there. 

= Is a member controlling function included? =

Not include. Please use the "membership" which is a standard function of WordPress, or compensate with other plug-in. 

== Screenshots ==

1. "Scheduler" page 
2. "Daily schedule" page 
3. "Weekly schedule" page
4. "Reservation form" page 
5. "Plugin option" page

== Changelog ==

See [Change log (ja)](http://olbsys.com/category/updates/)/[(en)](http://olbsys.com/en/category/updates/).

= 0.6.1 =

* The update process of a teacher's profile item "website" was improved. <br>
The item will be updated by "bulk action (edit post) ", also by "Import". 

= 0.6.0 =

* Profile edit by a teacher was changed a little. 
* Change of the term of validity by an administrator was changed a little. 
* New information feed from "olbsys.com" was added.

= 0.5.4 =

* The bug in the case of the profile edit and display by teacher user was corrected. 

= 0.5.3 =

* The filter hooks was added. Those are the receiver's addresses of the notice e-mail of reservation (or cancellation). 

= 0.5.2 =

* Malfunction was solved when used together with "[Events Manager](https://wordpress.org/plugins/events-manager/ )" etc.<br> 
(The malfunction is 404 errors when the subpage below an "Events" page is accessed, for example.) 

= 0.5.1 =

* Small bug fix. 

= 0.5.0 =

* "Calendar" short code was added. On "Daily Schedule" pages, the date can be chosen from a calendar.<br>
The type of a calendar is two kinds. They are "monthly" or "weekly".<br>
[&raquo; About Daily schedule (ja)](http://olbsys.com/setup/teachers/#daily_schedule)/[(en)](http://olbsys.com/en/setup/teachers/#daily_schedule).

* The teacher's self-portrait can be displayed on "Daily schedule".<br>
Set a "Featured Image" in each "post" of teacher information.<br>
[&raquo; About Teacher's portrait (ja)](http://olbsys.com/setup/teachers/#daily_schedule)/[(en)](http://olbsys.com/en/setup/teachers/#daily_schedule).

= 0.4.5 =

* The message in a "Ticket-logs" was changed partially.
* Bug fix

= 0.4.4 =

* With the output of Short-code in contents, a translation file (.mo file) is read according to the value of current locale information (get_locale()). <br>
(For example, in the cases of multilingualization etc.) <br />
However, the translation files which are attached at present are only Japanese and English. Sorry. 
* Bug fix

= 0.4.3 =

* Bug fix

= 0.4.2 =

* Bug fix

= 0.4.1 =

* The display style of "Ticket logs" was changed. 
* Also when the "Term of validity" is extended, it is displayed on "Ticket logs". 
* Bug fix

= 0.4.0 =

* The limit of the number of reservation per month can be specified. 
* "Ticket system" can be chosen. It is the system of giving each member tickets and making a reservation by consuming ticket. If tickets run short, the member has to purchase.
* Administrator can see the page which they use pretending to be a member or a teacher. 
* Some special pages were added and changed. 
* Some short-code were added. 
* Bug fix

= 0.3.1 =

* "Members info" page was added one of special page

= 0.3.0 =

* Table structure and processing were changed
* "Admin only" widget was added
* Bug fix

= 0.2.0.1 =

* Small bug fix

= 0.2.0 =

* first release.

== Upgrade Notice ==

= 0.6.1 =

The update process of a teacher's profile item "website" was improved.
