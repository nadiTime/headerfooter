<?php

function nevia_form_system_theme_settings_alter(&$form, $form_state) {

  $path = drupal_get_path('theme', 'nevia');
  drupal_add_library('system', 'ui');
  drupal_add_library('system', 'farbtastic');

  drupal_add_js($path . '/js/theme_admin.js');

  $form['settings'] = array(
      '#type' => 'vertical_tabs',
      '#title' => t('Theme settings'),
      '#weight' => 2,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
  );

  $form['settings']['general'] = array(
      '#type' => 'fieldset',
      '#title' => t('General settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
  );

  $form['settings']['general']['homepage_title'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable homepage title'),
      '#default_value' => theme_get_setting('homepage_title', 'nevia'),
  );
  $form['settings']['general']['main_menu_style'] = array(
      '#type' => 'select',
      '#title' => t('Main menu style'),
      '#options' => array(
          'style-1' => t('Style 1'),
          'style-2' => t('Style 2'),
      ),
      '#default_value' => theme_get_setting('main_menu_style', 'nevia'),
  );

  $form['settings']['skin'] = array(
      '#type' => 'fieldset',
      '#title' => t('Skin settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
  );


  $form['settings']['skin']['nevia_theme_color'] = array(
      '#title' => t('Theme color'),
      '#type' => 'textfield',
      '#default_value' => theme_get_setting('nevia_theme_color', 'nevia'),
      '#attributes' => array('class' => array('input color')),
      '#description' => t('Default color hex code is: #169FE6'),
  );
  // bg background
  $dir = drupal_get_path('theme', 'nevia') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'bg';

  $files = file_scan_directory($dir, '/.*\.png/');


  $bg_files = array();
  if (!empty($files)) {
    foreach ($files as $file) {
      if (isset($file->filename)) {
        $bg_files[$file->filename] = $file->filename;
      }
    }
  }

  $form['settings']['skin']['theme_background_image'] = array(
      '#title' => t('Background image'),
      '#type' => 'select',
      '#default_value' => theme_get_setting('theme_background_image', 'nevia'),
      '#options' => $bg_files,
      '#description' => t('All images background in <strong>!url</strong>', array('!url' => $dir)),
  );

  $form['#submit'][] = '_nevia_form_submit';
}

function _nevia_form_submit($form, &$form_state) {
  $values = $form_state['values'];

  if (!empty($values['nevia_theme_color'])) {
    _save_css_color_file($values['nevia_theme_color']);
  }
}

function _save_css_color_file($color) {
  $file = 'css/colors.css';
  $style = _get_color_css_temp($color);

  $palette = 'nevia';
  $theme = 'nevia';
  $id = $theme . '_color_cache';  //'-' . substr(hash('sha256', serialize($palette) . microtime()), 0, 8);
  $paths['color'] = 'public://color';
  $paths['target'] = $paths['color'] . '/' . $id;





  foreach ($paths as $path) {
    file_prepare_directory($path, FILE_CREATE_DIRECTORY);
  }

  $paths['target'] = $paths['target'] . '/';
  $paths['id'] = $id;
  $paths['source'] = drupal_get_path('theme', $theme) . '/';
  $paths['files'] = $paths['map'] = array();


  $base = base_path() . dirname($paths['source'] . $file) . '/';
  _drupal_build_css_path(NULL, $base);

  $base_file = drupal_basename($file);

  $file = $paths['target'] . $base_file;

  $filepath = file_unmanaged_save_data($style, $file, FILE_EXISTS_REPLACE);

  variable_set('nevia_theme_color', $filepath);
  
}

function _get_color_css_temp($color) {

  $css = '
#top-line { background: ' . $color . '; }
#current,
#navigation ul li a.sf-depth-1.active,
#navigation ul li.active-trail a.sf-depth-1  { background-color: ' . $color . '; border-right: 1px solid ' . $color . '; }
.ls-fullwidth .ls-nav-next:hover,
.ls-fullwidth .ls-nav-prev:hover { background-color: ' . $color . '; }
.caption-color { background: ' . $color . '; }
.flexslider .flex-next:hover,
.flexslider .flex-prev:hover { background-color: ' . $color . '; }
.arl.active:hover,
.arr.active:hover { background-color: ' . $color . '; }
.portfolio-item:hover .item-description { border-top: 5px solid ' . $color . '; }
.highlight.color,
.skill-bar-content { background: ' . $color . '; }
.dropcap,
.tabs-nav li.active a,
ul.tabs li.active a,
#breadcrumbs ul li a { color: ' . $color . '; }
.search-btn-widget,.block-search .form-submit { background-color: ' . $color . '; }
.tags a:hover { background: ' . $color . '; }
.latest-post-blog img:hover { background: ' . $color . '; border: 1px solid ' . $color . '; }
.flickr-widget-blog a:hover { border: 5px solid ' . $color . '; }
.selected { color: ' . $color . ' !important; }
.filters-dropdown.active,
#portfolio-navi a:hover { background-color: ' . $color . '; }
.button.gray:hover,
.button.light:hover,
.button.color,
input[type="button"],
input[type="submit"],
input[type="button"]:focus,
input[type="submit"]:focus { background:' . $color . '; }
.tabs-nav li.active a,ul.tabs li.active a { border-top: 1px solid ' . $color . '; }
.ui-accordion .ui-accordion-header-active:hover,
.ui-accordion .ui-accordion-header-active { color: ' . $color . '; }
.ui-accordion-icon-active { background-color: ' . $color . '; }
.trigger.active a { color: ' . $color . '; }
.trigger.active .toggle-icon { background-color: ' . $color . '; }
.testimonials-author { color: ' . $color . '; }
.pagination .current { background: ' . $color . ' !important; }
.flickr-widget a:hover { border-color: ' . $color . '; }
.latest-shop-items img:hover { background: ' . $color . '; border: 1px solid ' . $color . '; }
.increase-value { background: ' . $color . '; }
.ui-widget-header { background: ' . $color . '; }';

  return $css;
}