-- phpMyAdmin SQL Dump
-- version 4.2.7
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2015 at 11:59 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: 'boffice'
--

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_html_dynamic'
--

CREATE TABLE IF NOT EXISTS boffice_html_dynamic (
boffice_html_dynamic_id int(11) NOT NULL,
  boffice_html_dynamic_name varchar(255) NOT NULL,
  boffice_html_dynamic_sub_class varchar(255) NOT NULL COMMENT 'the file and classname of the class that implements dynamic_base',
  boffice_html_dynamic_parameters varchar(255) NOT NULL,
  boffice_html_dynamic_parameters_label varchar(255) NOT NULL,
  boffice_html_order int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_html_group'
--

CREATE TABLE IF NOT EXISTS boffice_html_group (
boffice_html_group_id int(11) NOT NULL,
  boffice_html_page_id int(11) NOT NULL,
  boffice_html_group_functions_as enum('section','sidebar-right','sidebar-left') NOT NULL DEFAULT 'section',
  boffice_html_group_order int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_html_group_assignments'
--

CREATE TABLE IF NOT EXISTS boffice_html_group_assignments (
  boffice_html_group_id int(11) NOT NULL,
  boffice_html_static_id int(11) NOT NULL DEFAULT '0',
  boffice_html_dynamic_id int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_html_page'
--

CREATE TABLE IF NOT EXISTS boffice_html_page (
boffice_html_page_id int(11) NOT NULL,
  boffice_html_page_title varchar(255) NOT NULL,
  boffice_html_page_url varchar(255) NOT NULL,
  boffice_nav_class_id int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_html_static'
--

CREATE TABLE IF NOT EXISTS boffice_html_static (
boffice_html_static_id int(11) NOT NULL,
  boffice_html_static_content text NOT NULL,
  boffice_html_static_name varchar(255) NOT NULL,
  boffice_html_static_class varchar(255) NOT NULL,
  boffice_html_order int(11) NOT NULL DEFAULT '1',
  boffice_html_static_last_edited datetime NOT NULL,
  boffice_html_static_last_edited_by int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_nav'
--

CREATE TABLE IF NOT EXISTS boffice_nav (
boffice_nav_id int(11) NOT NULL,
  boffice_nav_class_id int(11) NOT NULL DEFAULT '1',
  boffice_html_page_id int(11) NOT NULL,
  boffice_nav_parent_id int(11) NOT NULL DEFAULT '0',
  boffice_nav_order int(11) NOT NULL DEFAULT '0',
  boffice_nav_display_name varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_nav_class'
--

CREATE TABLE IF NOT EXISTS boffice_nav_class (
boffice_nav_class_id int(11) NOT NULL,
  boffice_nav_class_label varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_notice'
--

CREATE TABLE IF NOT EXISTS boffice_notice (
boffice_notice_id int(11) NOT NULL,
  boffice_notice_acknowledged enum('0','1') NOT NULL DEFAULT '0',
  boffice_notice_datetime datetime NOT NULL,
  boffice_notice_relates_user_id int(11) NOT NULL DEFAULT '0',
  boffice_notice_relates_reservation_id int(11) NOT NULL DEFAULT '0',
  boffice_notice_message text NOT NULL,
  boffice_notice_severity enum('LOW','NORMAL','HIGH') NOT NULL DEFAULT 'NORMAL'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table 'boffice_properties'
--

CREATE TABLE IF NOT EXISTS boffice_properties (
  boffice_property_name varchar(255) NOT NULL,
  boffice_property_value varchar(255) NOT NULL,
  boffice_property_description varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'cart'
--

CREATE TABLE IF NOT EXISTS cart (
cart_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  ip varchar(255) NOT NULL,
  created datetime NOT NULL,
  modified datetime NOT NULL,
  accessed datetime NOT NULL,
  paid datetime NOT NULL,
  cart_is_active tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5524 ;

-- --------------------------------------------------------

--
-- Table structure for table 'cart_item'
--

CREATE TABLE IF NOT EXISTS cart_item (
cart_item_id int(11) NOT NULL,
  cart_id int(11) NOT NULL,
  purchasable_class varchar(255) NOT NULL,
  purchasable_class_id int(11) NOT NULL,
  resultant_class_id int(11) NOT NULL DEFAULT '0',
  quantity int(11) NOT NULL,
  cart_item_priced_as_patron_type_id int(11) NOT NULL DEFAULT '0',
  cart_item_added_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10061 ;

-- --------------------------------------------------------

--
-- Table structure for table 'giftcard_usage'
--

CREATE TABLE IF NOT EXISTS giftcard_usage (
giftcard_usage_id int(11) NOT NULL,
  purchasable_giftcard_instance_id int(11) NOT NULL,
  transaction_id int(11) NOT NULL,
  giftcard_usage_amount float(6,2) NOT NULL,
  cart_id int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'package'
--

CREATE TABLE IF NOT EXISTS package (
package_id int(11) NOT NULL,
  cart_item_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table 'package_usage'
--

CREATE TABLE IF NOT EXISTS package_usage (
package_usage_id int(11) NOT NULL,
  package_id int(11) NOT NULL,
  benefit_id int(11) NOT NULL,
  transaction_id int(11) NOT NULL,
  package_usage_deduction float(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table 'page_files'
--

CREATE TABLE IF NOT EXISTS page_files (
page_file_id int(11) NOT NULL,
  page_file_name varchar(255) NOT NULL,
  page_file_title varchar(255) NOT NULL,
  page_file_type varchar(255) NOT NULL,
  page_file_contents longblob NOT NULL,
  page_file_contents_date datetime NOT NULL,
  updated datetime NOT NULL,
  page_file_access_restricted tinyint(1) NOT NULL DEFAULT '0',
  page_file_size int(11) NOT NULL,
  `user` varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=876 ;

-- --------------------------------------------------------

--
-- Table structure for table 'page_files_cache'
--

CREATE TABLE IF NOT EXISTS page_files_cache (
page_file_cache_id int(11) NOT NULL,
  page_file_id int(11) NOT NULL,
  page_file_cache_height int(11) NOT NULL,
  page_file_cache_filesize int(11) NOT NULL,
  page_file_cache_datetime datetime NOT NULL,
  page_file_cache_data longblob NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table 'patron_types'
--

CREATE TABLE IF NOT EXISTS patron_types (
patron_type_id int(11) NOT NULL,
  patron_type_label varchar(255) NOT NULL,
  patron_type_publicly_selectable tinyint(1) NOT NULL DEFAULT '0',
  patron_type_description varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'payment_finacial_details'
--

CREATE TABLE IF NOT EXISTS payment_finacial_details (
payment_finacial_details_id int(11) NOT NULL,
  transaction_id int(11) NOT NULL,
  payment_finacial_details_amount float(5,2) NOT NULL,
  payment_finacial_details_status enum('APPROVED','PENDING','FAILED','NONTRANSIENT','VOIDED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  card_last_4 varchar(255) NOT NULL,
  card_expiry varchar(255) NOT NULL,
  card_last_name varchar(255) NOT NULL,
  card_first_name varchar(255) NOT NULL,
  card_address_line1 varchar(255) NOT NULL,
  card_address_zip varchar(255) NOT NULL,
  our_invoice_id varchar(255) NOT NULL,
  vendor varchar(255) NOT NULL,
  vendor_batch_id varchar(255) NOT NULL,
  vendor_invoice_id varchar(255) NOT NULL,
  vendor_batch_date datetime NOT NULL,
  vendor_gateway_fee float(7,4) NOT NULL,
  vendor_authorization_code varchar(255) NOT NULL,
  payment_method enum('Visa','MasterCard','Diners','AmericanExpress','Discover','Cash','Check','Giftcard','Comp','Legacy') NOT NULL,
  terminal varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5512 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable'
--

CREATE TABLE IF NOT EXISTS purchasable (
purchasable_id int(11) NOT NULL,
  purchasable_price float(8,2) NOT NULL,
  purchasable_status enum('ITEM_STATUS_AVAILABLE','ITEM_STATUS_UNAVAILABLE','ITEM_STATUS_SOLDOUT','') NOT NULL,
  purchasable_item_type enum('ITEM_TYPE_SEAT','ITEM_TYPE_GIFTCARD','ITEM_TYPE_PACKAGE','ITEM_TYPE_MERCHANDISE','ITEM_TYPE_REGISTRATION','ITEM_TYPE_SEAT_GENERAL','ITEM_TYPE_DONATION') NOT NULL,
  purchasable_quantity int(11) NOT NULL,
  purchasable_tax_rate float(5,5) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_donation'
--

CREATE TABLE IF NOT EXISTS purchasable_donation (
purchasable_donation_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  purchasable_donation_datetime datetime NOT NULL,
  purchasable_donation_value float(6,2) NOT NULL,
  purchasable_donation_status enum('PENDING','CONFIRMED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  purchasable_donation_note text NOT NULL,
  transaction_id int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_giftcard_instance'
--

CREATE TABLE IF NOT EXISTS purchasable_giftcard_instance (
purchasable_giftcard_instance_id int(11) NOT NULL,
  purchasable_giftcard_instance_starting_value int(11) NOT NULL,
  purchasable_giftcard_instance_human_id varchar(255) NOT NULL,
  purchasable_giftcard_instance_human_key varchar(255) NOT NULL,
  purchasable_giftcard_instance_robot_url varchar(255) NOT NULL,
  purchasable_giftcard_instance_to varchar(255) NOT NULL,
  purchasable_giftcard_instance_from varchar(255) NOT NULL,
  purchasable_giftcard_instance_send_method enum('email','print','ship') NOT NULL,
  purchasable_giftcard_instance_send_data varchar(255) NOT NULL,
  purchasable_giftcard_instance_created datetime NOT NULL,
  purchasable_giftcard_instance_activated tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_package_model'
--

CREATE TABLE IF NOT EXISTS purchasable_package_model (
package_model_id int(11) NOT NULL,
  package_model_name varchar(255) NOT NULL,
  package_model_description varchar(255) NOT NULL,
  package_model_date_available datetime NOT NULL,
  package_model_date_close datetime NOT NULL,
  package_model_duration_in_days int(11) NOT NULL,
  package_model_is_single_use tinyint(1) NOT NULL DEFAULT '0',
  package_model_cost float(5,2) NOT NULL,
  package_model_patron_type_id int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_package_model_benefit'
--

CREATE TABLE IF NOT EXISTS purchasable_package_model_benefit (
package_model_benefit_id int(11) NOT NULL,
  package_model_id int(11) NOT NULL,
  package_model_benefit_label varchar(255) NOT NULL,
  package_model_benefit_type enum('PACKAGE_MODEL_BENEFIT_TYPE_TICKET','PACKAGE_MODEL_BENEFIT_TYPE_CONCESSION','PACKAGE_MODEL_BENEFIT_TYPE_MERCHANDISE','') NOT NULL,
  package_model_benefit_type_class varchar(255) NOT NULL,
  package_model_benefit_value float(4,2) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_registration'
--

CREATE TABLE IF NOT EXISTS purchasable_registration (
purchasable_registration_id int(11) NOT NULL,
  reg_name varchar(255) NOT NULL,
  reg_description text NOT NULL,
  reg_sales_available enum('ITEM_STATUS_AVAILABLE','ITEM_STATUS_UNAVAILABLE','ITEM_STATUS_SOLDOUT','') NOT NULL DEFAULT 'ITEM_STATUS_AVAILABLE',
  reg_img_url varchar(255) NOT NULL,
  reg_price float(5,2) NOT NULL,
  reg_quantity int(11) NOT NULL,
  reg_date_start datetime NOT NULL,
  reg_date_end datetime NOT NULL,
  reg_date_sales_start datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  reg_date_sales_end datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  purchasable_registration_category_id int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_registration_category'
--

CREATE TABLE IF NOT EXISTS purchasable_registration_category (
purchasable_registration_category_id int(11) NOT NULL,
  purchasable_registration_category_name varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_registration_instance'
--

CREATE TABLE IF NOT EXISTS purchasable_registration_instance (
purchasable_registration_instance_id int(11) NOT NULL,
  purchasable_registration_id int(11) NOT NULL,
  purchasable_registration_instance_datetime datetime NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_seat'
--

CREATE TABLE IF NOT EXISTS purchasable_seat (
purchasable_seat_id int(11) NOT NULL,
  purchasable_seat_abstract_id int(11) NOT NULL,
  show_seating_chart_id int(11) NOT NULL,
  show_seat_name varchar(255) NOT NULL,
  position_x int(11) NOT NULL,
  position_y int(11) NOT NULL,
  rotation int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=134 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_seating_general'
--

CREATE TABLE IF NOT EXISTS purchasable_seating_general (
purchasable_seating_general_id int(11) NOT NULL,
  show_instance_id int(11) NOT NULL,
  purchasable_seating_general_quantity_total int(11) NOT NULL,
  purchasable_seating_general_status enum('ITEM_STATUS_AVAILABLE','ITEM_STATUS_UNAVAILABLE','ITEM_STATUS_SOLDOUT','') NOT NULL DEFAULT 'ITEM_STATUS_UNAVAILABLE'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_seat_abstract'
--

CREATE TABLE IF NOT EXISTS purchasable_seat_abstract (
purchasable_seat_abstract_id int(11) NOT NULL,
  purchasable_id int(11) DEFAULT NULL,
  purchasable_seat_abstract_name varchar(255) NOT NULL,
  purchasable_seat_abstract_icon_available_url varchar(255) NOT NULL,
  purchasable_seat_abstract_icon_unavailable_url varchar(255) NOT NULL,
  purchasable_seat_abstract_price_multiplier float(5,4) NOT NULL DEFAULT '1.0000'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table 'purchasable_seat_instance'
--

CREATE TABLE IF NOT EXISTS purchasable_seat_instance (
purchasable_seat_instance_id int(11) NOT NULL,
  purchasable_seat_id int(11) NOT NULL,
  show_instance_id int(11) NOT NULL,
  seat_status enum('SEAT_STATUS_AVAILABLE','SEAT_STATUS_RESERVED','SEAT_STATUS_UNAVAILABLE','') NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14257 ;

-- --------------------------------------------------------

--
-- Table structure for table 'registrations'
--

CREATE TABLE IF NOT EXISTS registrations (
registration_id int(11) NOT NULL,
  transaction_id int(11) NOT NULL,
  registration_status enum('ACTIVE','CANCELLED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=501 ;

-- --------------------------------------------------------

--
-- Table structure for table 'reservation'
--

CREATE TABLE IF NOT EXISTS reservation (
reservation_id int(11) NOT NULL,
  reservation_status enum('ACTIVE','CANCELLED','REDEEMED','EXPIRED_NOT_REDEEMED') NOT NULL,
  transaction_id int(11) NOT NULL,
  reservation_note varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4019 ;

-- --------------------------------------------------------

--
-- Table structure for table 'reservation_ticket'
--

CREATE TABLE IF NOT EXISTS reservation_ticket (
reservation_ticket_id int(11) NOT NULL,
  purchasable_seat_instance_id int(11) NOT NULL DEFAULT '0',
  purchasable_seating_general int(11) NOT NULL DEFAULT '0',
  reservation_ticket_robot_url varchar(255) NOT NULL,
  reservation_ticket_robot_barcode varchar(255) NOT NULL,
  reservation_id int(11) NOT NULL,
  reservation_ticket_status enum('active','checked_in','lost','stolen','cancelled') NOT NULL DEFAULT 'active',
  reservation_ticket_created datetime NOT NULL,
  reservation_ticket_updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12034 ;

-- --------------------------------------------------------

--
-- Table structure for table 'seating_chart'
--

CREATE TABLE IF NOT EXISTS seating_chart (
seating_chart_id int(11) NOT NULL,
  seating_chart_name_internal varchar(255) NOT NULL,
  seating_chart_row_names varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table 'seating_chart_extras'
--

CREATE TABLE IF NOT EXISTS seating_chart_extras (
seating_chart_extra_id int(11) NOT NULL,
  seating_chart_extra_name varchar(255) NOT NULL,
  seating_chart_extra_icon_url varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'seating_chart_extras_instance'
--

CREATE TABLE IF NOT EXISTS seating_chart_extras_instance (
seating_chart_extras_instance_id int(11) NOT NULL,
  seating_chart_extra_id int(11) NOT NULL,
  seating_chart_id int(11) NOT NULL,
  seating_chart_extras_instance_x int(11) NOT NULL,
  seating_chart_extras_instance_y int(11) NOT NULL,
  seating_chart_extras_instance_rotation int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table 'seat_price_by_general_seating_by_patron_type'
--

CREATE TABLE IF NOT EXISTS seat_price_by_general_seating_by_patron_type (
seat_price_by_general_seating_by_patron_type_id int(11) NOT NULL,
  patron_type_id int(11) NOT NULL,
  purchasable_seating_general_id int(11) NOT NULL,
  price_multiplier float(4,3) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'seat_price_by_patron_type'
--

CREATE TABLE IF NOT EXISTS seat_price_by_patron_type (
seat_price_by_patron_type_id int(11) NOT NULL,
  patron_type_id int(11) NOT NULL,
  purchasable_seat_abstract_id int(11) NOT NULL,
  price_multiplier float(6,5) NOT NULL DEFAULT '1.00000'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table 'shows'
--

CREATE TABLE IF NOT EXISTS shows (
show_id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  url_name varchar(255) NOT NULL,
  description text NOT NULL,
  cover_image_url varchar(255) NOT NULL,
  seating_chart_id int(11) NOT NULL,
  seating_chart_general_count int(11) NOT NULL DEFAULT '0',
  stage_id int(11) NOT NULL,
  show_base_price float(4,2) NOT NULL,
  show_seat_price_model varchar(255) NOT NULL DEFAULT 'show_seat_price_model_patron_type'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_image_assignments'
--

CREATE TABLE IF NOT EXISTS show_image_assignments (
  show_id int(11) NOT NULL,
  show_image_type_id int(11) NOT NULL,
  page_file_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'show_image_types'
--

CREATE TABLE IF NOT EXISTS show_image_types (
show_image_type_id int(11) NOT NULL,
  show_image_type_name varchar(255) NOT NULL,
  show_image_type_description varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_instance'
--

CREATE TABLE IF NOT EXISTS show_instance (
show_instance_id int(11) NOT NULL,
  show_id int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  instance_sale_enabled tinyint(1) NOT NULL DEFAULT '1',
  seating_chart_html_cache text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=109 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_instance_cache'
--

CREATE TABLE IF NOT EXISTS show_instance_cache (
  show_instance_id int(11) NOT NULL,
  show_instance_cache_time datetime NOT NULL,
  show_instance_cache_total int(11) NOT NULL,
  show_instance_cache_reserved int(11) NOT NULL,
  show_instance_cache_available int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'show_instance_workers'
--

CREATE TABLE IF NOT EXISTS show_instance_workers (
show_instance_worker_id int(11) NOT NULL,
  user_id int(11) NOT NULL DEFAULT '0',
  show_instance_worker_plus_one enum('0','1') NOT NULL DEFAULT '0',
  show_instance_worker_type_id int(11) NOT NULL,
  show_instance_id int(11) NOT NULL,
  show_instance_worker_status enum('UNFILLED','FILLED') NOT NULL DEFAULT 'UNFILLED'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_instance_worker_types'
--

CREATE TABLE IF NOT EXISTS show_instance_worker_types (
show_instance_worker_type_id int(11) NOT NULL,
  show_instance_worker_type_name varchar(255) NOT NULL,
  show_instance_worker_type_description varchar(255) NOT NULL,
  show_instance_worker_type_requirements varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_people'
--

CREATE TABLE IF NOT EXISTS show_people (
  show_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  show_people_role varchar(255) NOT NULL,
  show_people_role_type enum('CAST','PRIMARY_CAST','CREW','PRIMARY_CREW') NOT NULL DEFAULT 'CAST'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'show_series'
--

CREATE TABLE IF NOT EXISTS show_series (
show_series_id int(11) NOT NULL,
  show_series_name varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table 'show_series_assignment'
--

CREATE TABLE IF NOT EXISTS show_series_assignment (
  show_series_id int(11) NOT NULL,
  show_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table 'stage'
--

CREATE TABLE IF NOT EXISTS stage (
stage_id int(11) NOT NULL,
  stage_name varchar(255) NOT NULL,
  stage_description varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table 'transaction'
--

CREATE TABLE IF NOT EXISTS `transaction` (
transaction_id int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  user_id int(11) NOT NULL,
  cart_id int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5512 ;

-- --------------------------------------------------------

--
-- Table structure for table 'users'
--

CREATE TABLE IF NOT EXISTS users (
user_id int(11) NOT NULL,
  ulogin_id int(11) NOT NULL,
  user_name_last varchar(255) NOT NULL,
  user_name_first varchar(255) NOT NULL,
  user_email varchar(255) NOT NULL,
  user_address_line1 varchar(255) NOT NULL,
  user_address_line2 varchar(255) NOT NULL,
  user_city varchar(255) NOT NULL,
  user_state varchar(255) NOT NULL,
  user_zip varchar(255) NOT NULL,
  user_account_value float(4,2) NOT NULL DEFAULT '0.00',
  patron_type_id int(11) NOT NULL DEFAULT '0',
  user_note varchar(255) NOT NULL,
  user_reservation_reminders tinyint(1) NOT NULL DEFAULT '1',
  user_email_list tinyint(1) NOT NULL DEFAULT '0',
  user_is_company tinyint(1) NOT NULL DEFAULT '0',
  user_is_volunteer tinyint(1) NOT NULL DEFAULT '0',
  user_is_donor tinyint(4) NOT NULL DEFAULT '0',
  user_is_selectable tinyint(1) NOT NULL DEFAULT '0',
  user_is_office_admin tinyint(1) NOT NULL DEFAULT '0',
  user_is_show_admin tinyint(1) NOT NULL DEFAULT '0',
  user_is_finacial_admin tinyint(1) NOT NULL DEFAULT '0',
  user_is_class_admin tinyint(1) NOT NULL DEFAULT '0',
  user_last_login datetime NOT NULL,
  user_bio text NOT NULL,
  user_img_url varchar(255) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=502 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table boffice_html_dynamic
--
ALTER TABLE boffice_html_dynamic
 ADD PRIMARY KEY (boffice_html_dynamic_id);

--
-- Indexes for table boffice_html_group
--
ALTER TABLE boffice_html_group
 ADD PRIMARY KEY (boffice_html_group_id), ADD KEY boffice_html_page_id (boffice_html_page_id);

--
-- Indexes for table boffice_html_group_assignments
--
ALTER TABLE boffice_html_group_assignments
 ADD KEY boffice_html_group_id (boffice_html_group_id,boffice_html_static_id);

--
-- Indexes for table boffice_html_page
--
ALTER TABLE boffice_html_page
 ADD PRIMARY KEY (boffice_html_page_id), ADD UNIQUE KEY boffice_html_page_url (boffice_html_page_url);

--
-- Indexes for table boffice_html_static
--
ALTER TABLE boffice_html_static
 ADD PRIMARY KEY (boffice_html_static_id);

--
-- Indexes for table boffice_nav
--
ALTER TABLE boffice_nav
 ADD PRIMARY KEY (boffice_nav_id), ADD KEY boffice_html_page_id (boffice_html_page_id,boffice_nav_parent_id,boffice_nav_order);

--
-- Indexes for table boffice_nav_class
--
ALTER TABLE boffice_nav_class
 ADD PRIMARY KEY (boffice_nav_class_id);

--
-- Indexes for table boffice_notice
--
ALTER TABLE boffice_notice
 ADD PRIMARY KEY (boffice_notice_id);

--
-- Indexes for table boffice_properties
--
ALTER TABLE boffice_properties
 ADD PRIMARY KEY (boffice_property_name);

--
-- Indexes for table cart
--
ALTER TABLE cart
 ADD PRIMARY KEY (cart_id), ADD KEY user_id (user_id), ADD KEY created (created), ADD KEY paid (paid), ADD KEY cart_is_active (cart_is_active);

--
-- Indexes for table cart_item
--
ALTER TABLE cart_item
 ADD PRIMARY KEY (cart_item_id), ADD KEY cart_id (cart_id), ADD KEY purchasable_class (purchasable_class), ADD KEY purchasable_class_id (purchasable_class_id), ADD KEY cart_item_added_datetime (cart_item_added_datetime), ADD KEY purchasable_class_2 (purchasable_class,purchasable_class_id), ADD KEY resultant_class_id (resultant_class_id);

--
-- Indexes for table giftcard_usage
--
ALTER TABLE giftcard_usage
 ADD PRIMARY KEY (giftcard_usage_id), ADD KEY purchasable_giftcard_instance_id (purchasable_giftcard_instance_id), ADD KEY cart_id (cart_id);

--
-- Indexes for table package
--
ALTER TABLE package
 ADD PRIMARY KEY (package_id), ADD KEY cart_item_id (cart_item_id);

--
-- Indexes for table package_usage
--
ALTER TABLE package_usage
 ADD PRIMARY KEY (package_usage_id), ADD KEY package_id (package_id), ADD KEY benefit_id (benefit_id), ADD KEY transaction_id (transaction_id);

--
-- Indexes for table page_files
--
ALTER TABLE page_files
 ADD PRIMARY KEY (page_file_id), ADD KEY updated (updated);

--
-- Indexes for table page_files_cache
--
ALTER TABLE page_files_cache
 ADD PRIMARY KEY (page_file_cache_id), ADD KEY asset_data_id (page_file_id), ADD KEY page_file_cache_id (page_file_cache_id), ADD KEY page_file_id (page_file_id,page_file_cache_height,page_file_cache_datetime);

--
-- Indexes for table patron_types
--
ALTER TABLE patron_types
 ADD PRIMARY KEY (patron_type_id);

--
-- Indexes for table payment_finacial_details
--
ALTER TABLE payment_finacial_details
 ADD PRIMARY KEY (payment_finacial_details_id), ADD KEY transaction_id (transaction_id);

--
-- Indexes for table purchasable
--
ALTER TABLE purchasable
 ADD PRIMARY KEY (purchasable_id), ADD KEY purchasable_status (purchasable_status), ADD KEY purchasable_item_type (purchasable_item_type);

--
-- Indexes for table purchasable_donation
--
ALTER TABLE purchasable_donation
 ADD PRIMARY KEY (purchasable_donation_id);

--
-- Indexes for table purchasable_giftcard_instance
--
ALTER TABLE purchasable_giftcard_instance
 ADD PRIMARY KEY (purchasable_giftcard_instance_id), ADD KEY purchasable_giftcard_instance_human_id (purchasable_giftcard_instance_human_id), ADD KEY purchasable_giftcard_instance_created (purchasable_giftcard_instance_created), ADD KEY purchasable_giftcard_instance_robot_url (purchasable_giftcard_instance_robot_url);

--
-- Indexes for table purchasable_package_model
--
ALTER TABLE purchasable_package_model
 ADD PRIMARY KEY (package_model_id), ADD KEY package_model_patron_type_id (package_model_patron_type_id), ADD KEY package_model_date_available (package_model_date_available), ADD KEY package_model_date_close (package_model_date_close), ADD KEY package_model_name (package_model_name);

--
-- Indexes for table purchasable_package_model_benefit
--
ALTER TABLE purchasable_package_model_benefit
 ADD PRIMARY KEY (package_model_benefit_id), ADD KEY package_model_id (package_model_id), ADD KEY package_model_benefit_type (package_model_benefit_type), ADD KEY package_model_id_2 (package_model_id,package_model_benefit_type);

--
-- Indexes for table purchasable_registration
--
ALTER TABLE purchasable_registration
 ADD PRIMARY KEY (purchasable_registration_id), ADD KEY reg_sales_available (reg_sales_available), ADD KEY reg_date_start (reg_date_start), ADD KEY reg_date_end (reg_date_end), ADD KEY reg_date_sales_start (reg_date_sales_start), ADD KEY reg_date_sales_end (reg_date_sales_end), ADD KEY purchasable_registration_category_id (purchasable_registration_category_id);

--
-- Indexes for table purchasable_registration_category
--
ALTER TABLE purchasable_registration_category
 ADD PRIMARY KEY (purchasable_registration_category_id), ADD KEY purchasable_registration_category_name (purchasable_registration_category_name);

--
-- Indexes for table purchasable_registration_instance
--
ALTER TABLE purchasable_registration_instance
 ADD PRIMARY KEY (purchasable_registration_instance_id), ADD KEY purchasable_registration_id (purchasable_registration_id), ADD KEY purchasable_registration_instance_datetime (purchasable_registration_instance_datetime);

--
-- Indexes for table purchasable_seat
--
ALTER TABLE purchasable_seat
 ADD PRIMARY KEY (purchasable_seat_id), ADD KEY purchasable_seat_abstract_id (purchasable_seat_abstract_id), ADD KEY show_seating_chart_id (show_seating_chart_id);

--
-- Indexes for table purchasable_seating_general
--
ALTER TABLE purchasable_seating_general
 ADD PRIMARY KEY (purchasable_seating_general_id);

--
-- Indexes for table purchasable_seat_abstract
--
ALTER TABLE purchasable_seat_abstract
 ADD PRIMARY KEY (purchasable_seat_abstract_id), ADD KEY purchasable_id (purchasable_id), ADD KEY purchasable_seat_abstract_name (purchasable_seat_abstract_name);

--
-- Indexes for table purchasable_seat_instance
--
ALTER TABLE purchasable_seat_instance
 ADD PRIMARY KEY (purchasable_seat_instance_id), ADD KEY purchasable_seat_id (purchasable_seat_id), ADD KEY show_instance_id (show_instance_id), ADD KEY seat_status (seat_status);

--
-- Indexes for table registrations
--
ALTER TABLE registrations
 ADD PRIMARY KEY (registration_id), ADD KEY transaction_id (transaction_id), ADD KEY registration_status (registration_status);

--
-- Indexes for table reservation
--
ALTER TABLE reservation
 ADD PRIMARY KEY (reservation_id), ADD KEY transaction_id (transaction_id), ADD KEY reservation_status (reservation_status);

--
-- Indexes for table reservation_ticket
--
ALTER TABLE reservation_ticket
 ADD PRIMARY KEY (reservation_ticket_id), ADD KEY purchasable_seat_instance_id (purchasable_seat_instance_id,reservation_ticket_robot_url,reservation_ticket_robot_barcode,reservation_id);

--
-- Indexes for table seating_chart
--
ALTER TABLE seating_chart
 ADD PRIMARY KEY (seating_chart_id), ADD KEY seating_chart_name_internal (seating_chart_name_internal);

--
-- Indexes for table seating_chart_extras
--
ALTER TABLE seating_chart_extras
 ADD PRIMARY KEY (seating_chart_extra_id), ADD KEY seating_chart_extra_name (seating_chart_extra_name);

--
-- Indexes for table seating_chart_extras_instance
--
ALTER TABLE seating_chart_extras_instance
 ADD PRIMARY KEY (seating_chart_extras_instance_id), ADD KEY seating_chart_extra_id (seating_chart_extra_id), ADD KEY seating_chart_id (seating_chart_id);

--
-- Indexes for table seat_price_by_general_seating_by_patron_type
--
ALTER TABLE seat_price_by_general_seating_by_patron_type
 ADD PRIMARY KEY (seat_price_by_general_seating_by_patron_type_id);

--
-- Indexes for table seat_price_by_patron_type
--
ALTER TABLE seat_price_by_patron_type
 ADD PRIMARY KEY (seat_price_by_patron_type_id), ADD KEY patron_type_id (patron_type_id), ADD KEY purchasable_seat_abstract_id (purchasable_seat_abstract_id);

--
-- Indexes for table shows
--
ALTER TABLE shows
 ADD PRIMARY KEY (show_id), ADD KEY stage_id (stage_id), ADD KEY seating_chart_id (seating_chart_id);

--
-- Indexes for table show_image_assignments
--
ALTER TABLE show_image_assignments
 ADD KEY show_id (show_id), ADD KEY show_image_type_id (show_image_type_id);

--
-- Indexes for table show_image_types
--
ALTER TABLE show_image_types
 ADD PRIMARY KEY (show_image_type_id);

--
-- Indexes for table show_instance
--
ALTER TABLE show_instance
 ADD PRIMARY KEY (show_instance_id), ADD KEY show_id (show_id), ADD KEY `datetime` (`datetime`), ADD KEY instance_sale_enabled (instance_sale_enabled);

--
-- Indexes for table show_instance_cache
--
ALTER TABLE show_instance_cache
 ADD KEY show_instance_id (show_instance_id,show_instance_cache_time);

--
-- Indexes for table show_instance_workers
--
ALTER TABLE show_instance_workers
 ADD PRIMARY KEY (show_instance_worker_id), ADD KEY show_instance_id (user_id), ADD KEY show_instance_worker_type_id (show_instance_worker_type_id,show_instance_id);

--
-- Indexes for table show_instance_worker_types
--
ALTER TABLE show_instance_worker_types
 ADD PRIMARY KEY (show_instance_worker_type_id);

--
-- Indexes for table show_people
--
ALTER TABLE show_people
 ADD KEY show_id (show_id,user_id);

--
-- Indexes for table show_series
--
ALTER TABLE show_series
 ADD PRIMARY KEY (show_series_id);

--
-- Indexes for table show_series_assignment
--
ALTER TABLE show_series_assignment
 ADD KEY show_series_id (show_series_id,show_id);

--
-- Indexes for table stage
--
ALTER TABLE stage
 ADD PRIMARY KEY (stage_id), ADD KEY stage_name (stage_name);

--
-- Indexes for table transaction
--
ALTER TABLE transaction
 ADD PRIMARY KEY (transaction_id), ADD KEY `datetime` (`datetime`), ADD KEY user_id (user_id), ADD KEY cart_id (cart_id);

--
-- Indexes for table users
--
ALTER TABLE users
 ADD PRIMARY KEY (user_id), ADD KEY ulogin_id (ulogin_id), ADD KEY patron_type_id (patron_type_id), ADD KEY user_email (user_email), ADD KEY user_name_last (user_name_last);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table boffice_html_dynamic
--
ALTER TABLE boffice_html_dynamic
MODIFY boffice_html_dynamic_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table boffice_html_group
--
ALTER TABLE boffice_html_group
MODIFY boffice_html_group_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table boffice_html_page
--
ALTER TABLE boffice_html_page
MODIFY boffice_html_page_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table boffice_html_static
--
ALTER TABLE boffice_html_static
MODIFY boffice_html_static_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table boffice_nav
--
ALTER TABLE boffice_nav
MODIFY boffice_nav_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table boffice_nav_class
--
ALTER TABLE boffice_nav_class
MODIFY boffice_nav_class_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table boffice_notice
--
ALTER TABLE boffice_notice
MODIFY boffice_notice_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table cart
--
ALTER TABLE cart
MODIFY cart_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5524;
--
-- AUTO_INCREMENT for table cart_item
--
ALTER TABLE cart_item
MODIFY cart_item_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10061;
--
-- AUTO_INCREMENT for table giftcard_usage
--
ALTER TABLE giftcard_usage
MODIFY giftcard_usage_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table package
--
ALTER TABLE package
MODIFY package_id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table package_usage
--
ALTER TABLE package_usage
MODIFY package_usage_id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table page_files
--
ALTER TABLE page_files
MODIFY page_file_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=876;
--
-- AUTO_INCREMENT for table page_files_cache
--
ALTER TABLE page_files_cache
MODIFY page_file_cache_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table patron_types
--
ALTER TABLE patron_types
MODIFY patron_type_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table payment_finacial_details
--
ALTER TABLE payment_finacial_details
MODIFY payment_finacial_details_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5512;
--
-- AUTO_INCREMENT for table purchasable
--
ALTER TABLE purchasable
MODIFY purchasable_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table purchasable_donation
--
ALTER TABLE purchasable_donation
MODIFY purchasable_donation_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table purchasable_giftcard_instance
--
ALTER TABLE purchasable_giftcard_instance
MODIFY purchasable_giftcard_instance_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table purchasable_package_model
--
ALTER TABLE purchasable_package_model
MODIFY package_model_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table purchasable_package_model_benefit
--
ALTER TABLE purchasable_package_model_benefit
MODIFY package_model_benefit_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table purchasable_registration
--
ALTER TABLE purchasable_registration
MODIFY purchasable_registration_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table purchasable_registration_category
--
ALTER TABLE purchasable_registration_category
MODIFY purchasable_registration_category_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table purchasable_registration_instance
--
ALTER TABLE purchasable_registration_instance
MODIFY purchasable_registration_instance_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=41;
--
-- AUTO_INCREMENT for table purchasable_seat
--
ALTER TABLE purchasable_seat
MODIFY purchasable_seat_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=134;
--
-- AUTO_INCREMENT for table purchasable_seating_general
--
ALTER TABLE purchasable_seating_general
MODIFY purchasable_seating_general_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=66;
--
-- AUTO_INCREMENT for table purchasable_seat_abstract
--
ALTER TABLE purchasable_seat_abstract
MODIFY purchasable_seat_abstract_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table purchasable_seat_instance
--
ALTER TABLE purchasable_seat_instance
MODIFY purchasable_seat_instance_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14257;
--
-- AUTO_INCREMENT for table registrations
--
ALTER TABLE registrations
MODIFY registration_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=501;
--
-- AUTO_INCREMENT for table reservation
--
ALTER TABLE reservation
MODIFY reservation_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4019;
--
-- AUTO_INCREMENT for table reservation_ticket
--
ALTER TABLE reservation_ticket
MODIFY reservation_ticket_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12034;
--
-- AUTO_INCREMENT for table seating_chart
--
ALTER TABLE seating_chart
MODIFY seating_chart_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table seating_chart_extras
--
ALTER TABLE seating_chart_extras
MODIFY seating_chart_extra_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table seating_chart_extras_instance
--
ALTER TABLE seating_chart_extras_instance
MODIFY seating_chart_extras_instance_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table seat_price_by_general_seating_by_patron_type
--
ALTER TABLE seat_price_by_general_seating_by_patron_type
MODIFY seat_price_by_general_seating_by_patron_type_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table seat_price_by_patron_type
--
ALTER TABLE seat_price_by_patron_type
MODIFY seat_price_by_patron_type_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table shows
--
ALTER TABLE shows
MODIFY show_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table show_image_types
--
ALTER TABLE show_image_types
MODIFY show_image_type_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table show_instance
--
ALTER TABLE show_instance
MODIFY show_instance_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=109;
--
-- AUTO_INCREMENT for table show_instance_workers
--
ALTER TABLE show_instance_workers
MODIFY show_instance_worker_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table show_instance_worker_types
--
ALTER TABLE show_instance_worker_types
MODIFY show_instance_worker_type_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table show_series
--
ALTER TABLE show_series
MODIFY show_series_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table stage
--
ALTER TABLE stage
MODIFY stage_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table transaction
--
ALTER TABLE transaction
MODIFY transaction_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5512;
--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
MODIFY user_id int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=502;
--
-- Constraints for dumped tables
--

--
-- Constraints for table package
--
ALTER TABLE package
ADD CONSTRAINT package_ibfk_1 FOREIGN KEY (cart_item_id) REFERENCES cart_item (cart_item_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table package_usage
--
ALTER TABLE package_usage
ADD CONSTRAINT package_usage_ibfk_1 FOREIGN KEY (package_id) REFERENCES package (package_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT package_usage_ibfk_2 FOREIGN KEY (benefit_id) REFERENCES purchasable_package_model_benefit (package_model_benefit_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT package_usage_ibfk_3 FOREIGN KEY (transaction_id) REFERENCES transaction (transaction_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table page_files_cache
--
ALTER TABLE page_files_cache
ADD CONSTRAINT page_files_cache_ibfk_1 FOREIGN KEY (page_file_id) REFERENCES page_files (page_file_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table purchasable_registration
--
ALTER TABLE purchasable_registration
ADD CONSTRAINT purchasable_registration_ibfk_1 FOREIGN KEY (purchasable_registration_category_id) REFERENCES purchasable_registration_category (purchasable_registration_category_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table purchasable_seat
--
ALTER TABLE purchasable_seat
ADD CONSTRAINT purchasable_seat_ibfk_1 FOREIGN KEY (purchasable_seat_abstract_id) REFERENCES purchasable_seat_abstract (purchasable_seat_abstract_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT purchasable_seat_ibfk_2 FOREIGN KEY (show_seating_chart_id) REFERENCES seating_chart (seating_chart_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table purchasable_seat_abstract
--
ALTER TABLE purchasable_seat_abstract
ADD CONSTRAINT purchasable_seat_abstract_ibfk_1 FOREIGN KEY (purchasable_id) REFERENCES purchasable (purchasable_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table purchasable_seat_instance
--
ALTER TABLE purchasable_seat_instance
ADD CONSTRAINT purchasable_seat_instance_ibfk_1 FOREIGN KEY (purchasable_seat_id) REFERENCES purchasable_seat (purchasable_seat_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT purchasable_seat_instance_ibfk_2 FOREIGN KEY (show_instance_id) REFERENCES show_instance (show_instance_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table registrations
--
ALTER TABLE registrations
ADD CONSTRAINT registrations_ibfk_1 FOREIGN KEY (transaction_id) REFERENCES transaction (transaction_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table reservation
--
ALTER TABLE reservation
ADD CONSTRAINT reservation_ibfk_1 FOREIGN KEY (transaction_id) REFERENCES transaction (transaction_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table seating_chart_extras_instance
--
ALTER TABLE seating_chart_extras_instance
ADD CONSTRAINT seating_chart_extras_instance_ibfk_1 FOREIGN KEY (seating_chart_extra_id) REFERENCES seating_chart_extras (seating_chart_extra_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT seating_chart_extras_instance_ibfk_2 FOREIGN KEY (seating_chart_id) REFERENCES seating_chart (seating_chart_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table seat_price_by_patron_type
--
ALTER TABLE seat_price_by_patron_type
ADD CONSTRAINT seat_price_by_patron_type_ibfk_1 FOREIGN KEY (patron_type_id) REFERENCES patron_types (patron_type_id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table transaction
--
ALTER TABLE transaction
ADD CONSTRAINT transaction_ibfk_1 FOREIGN KEY (cart_id) REFERENCES cart (cart_id) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
