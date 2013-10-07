=== Online Lesson Booking ===
Contributors: tnomi
Donate link: 
Tags: booking, reservation, appointment, timetable, lesson 
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 0.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plug-in supplies the reservation-form and scheduler for the one-to-one online lesson. 

== Description ==

Online Lesson Booking system (OLB) was made in order to equip a web site with the reservation-form and scheduler for one-to-one online lesson.

Teacher (author) sets up a timetable using a scheduler, and member (subscriber) clicks and reserves a timetable.
Teacher and a member are informed by e-mail in the case of reservation and cancellation. 

== Installation ==

= Installation =

1. Donwload plugin file and unzip it.
2. Upload `online-lesson-booking-system` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the `Plugins` menu in WordPress

= Plugin set up =

1. Open the WordPress admin panel, and go to the plugin option page `OLBsystem`.
2. Menu `OLBsystem > General` is setup about reservation and a timetable.
3. Menu `OLBsystem > Special pages` is setup of the name (slug) of a page indispensable to a system.
4. Menu `OLBsystem > Mail` is Edit of the text of notice mail.
5. This plug-in uses JQuery. Insert in your theme-file(functions.php) the code `wp_enqueue_script('jquery');`. 
6. Some special pages are already created, when the plug-in was activated.
7. Activate added widget `Members only` and `Teachers only`.

= Edit the schedule of teacher = 
1. Add some users as teacher. Teacher's role is  `author`.
2. Open the their profile-edit-page, check the item of `teacher`.
3. Log in as a teacher. Access the `editschedule` page and set a schedule.
4. Make the information of each teacher as `post` (ex. with `teacher` category, etc.), and insert short cord [olb_weekly_schedule id="xx"].

* `id` is ID number of each teacher. ID number is confirmed with a list of users in admin-page.

= Member registration =

1. Check the item of the `membership` (anyone can register) in the admin page of WordPress.
2. A `new user's default role` is `subscriber`. 
3. Members perform new user's registration themselves. Member must set item `Skype ID`.
4. Administrator update the item `term of validity` of member's profile. (ex. after checking the payment from a member, etc.)

* The member can reserve a lesson until the `term of validity`. 

== Frequently Asked Questions ==

= How is reservation information saved? =

An original table is created in a database, and it saves there. 

= Is a member controlling function included? =

Not include. Please use the `membership` which is a standard function of WordPress, or compensate with other plug-in. 

== Screenshots ==

1. `Scheduler` page 
2. `Weekly schedule` page
3. `Reservation form` page 
4. `Plugin option` page

== Changelog ==

= 0.2.0 =
* first release.

== Upgrade Notice ==

= 0.2.0 =

Nothing.
