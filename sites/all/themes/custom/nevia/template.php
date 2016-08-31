<?php

function _nevia_add_css() {
  $theme_path = path_to_theme();
  drupal_add_css($theme_path . '/css/base.css');
  drupal_add_css($theme_path . '/css/responsive.css');
  drupal_add_css($theme_path . '/css/icons.css');
  drupal_add_css($theme_path . '/css/style.css');
  drupal_add_css($theme_path . '/css/colors/blue.css');
  drupal_add_css($theme_path . '/css/nevia.css');

  $default_color = variable_get('nevia_theme_color');
  if (!empty($default_color)) {
    drupal_add_css($default_color);
  }
}

function nevia_preprocess_html(&$variables) {


  _nevia_add_css();

  drupal_add_html_head(
          array(
      '#tag' => 'meta',
      '#attributes' => array(
          'name' => 'viewport',
          'content' => 'width=device-width, initial-scale=1',
      ),
          ), 'centum:viewport_meta'
  );
}

function nevia_preprocess_page(&$variables) {

  if (!module_exists('jquery_update')) {
    drupal_set_message(t('Jquery update is required, <a target="_blank" href="!url">Download it</a>,  install and switch jquery to version 1.7', array('!url' => 'http://drupal.org/project/jquery_update')), 'warning');
  }

  $page = $variables['page'];


  $content_class = 'sixteen columns';

  $container_class = "";
  if ($page['sidebar_first'] || $page['sidebar_second']) {
    $content_class = "eleven floated right";
    $container_class = 'floated';
  }if ($page['sidebar_second']) {
    $content_class = 'eleven floated left';
  }
  $variables['containner_class'] = $container_class;
  $variables['content_class'] = $content_class;
  $variables['main_menu'] = menu_main_menu();
  $logos = $variables['logos'];
  $main_menu_tree = menu_tree_all_data('main-menu');
  $logged_user_menu = menu_tree_all_data('menu-logged-user-menu');
  $variables['header'] = test_innovators_header($main_menu_tree, $logged_user_menu, $logos);
}

function nevia_format_comma_field($field_category, $node, $limit = NULL) {

  if (module_exists('i18n_taxonomy')) {
    $language = i18n_language();
  }

  $category_arr = array();
  $category = '';
  $field = field_get_items('node', $node, $field_category);

  if (!empty($field)) {
    foreach ($field as $item) {
      $term = taxonomy_term_load($item['tid']);


      if ($term) {
        if (module_exists('i18n_taxonomy')) {
          $term_name = i18n_taxonomy_term_name($term, $language->language);

          // $term_desc = tagclouds_i18n_taxonomy_term_description($term, $language->language);
        } else {
          $term_name = $term->name;
          //$term_desc = $term->description;
        }

        $category_arr[] = l($term_name, 'taxonomy/term/' . $item['tid']);
      }

      if ($limit) {
        if (count($category_arr) == $limit) {
          $category = implode(', ', $category_arr);
          return $category;
        }
      }
    }
  }
  $category = implode(', ', $category_arr);

  return $category;
}

function nevia_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];

  if (!empty($breadcrumb)) {
    $output = '<ul><li>' . t('You are here') . ':</li>';

    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    foreach ($breadcrumb as $br) {
      $output .= '<li>' . $br . '</li>';
    }

    $output .= '</ul>';

    return $output;
  }
}

function nevia_table($variables) {
  $header = $variables['header'];
  $rows = $variables['rows'];
  $attributes = $variables['attributes'];
  $caption = $variables['caption'];
  $colgroups = $variables['colgroups'];
  $sticky = $variables['sticky'];
  $empty = $variables['empty'];

  // Add sticky headers, if applicable.
  if (count($header) && $sticky) {
    drupal_add_js('misc/tableheader.js');
    // Add 'sticky-enabled' class to the table to identify it for JS.
    // This is needed to target tables constructed by this function.
    $attributes['class'][] = 'sticky-enabled';
  }
  $attributes['class'][] = 'standard-table'; // added default table style.

  $output = '<table' . drupal_attributes($attributes) . ">\n";

  if (isset($caption)) {
    $output .= '<caption>' . $caption . "</caption>\n";
  }

  // Format the table columns:
  if (count($colgroups)) {
    foreach ($colgroups as $number => $colgroup) {
      $attributes = array();

      // Check if we're dealing with a simple or complex column
      if (isset($colgroup['data'])) {
        foreach ($colgroup as $key => $value) {
          if ($key == 'data') {
            $cols = $value;
          } else {
            $attributes[$key] = $value;
          }
        }
      } else {
        $cols = $colgroup;
      }

      // Build colgroup
      if (is_array($cols) && count($cols)) {
        $output .= ' <colgroup' . drupal_attributes($attributes) . '>';
        $i = 0;
        foreach ($cols as $col) {
          $output .= ' <col' . drupal_attributes($col) . ' />';
        }
        $output .= " </colgroup>\n";
      } else {
        $output .= ' <colgroup' . drupal_attributes($attributes) . " />\n";
      }
    }
  }

  // Add the 'empty' row message if available.
  if (!count($rows) && $empty) {
    $header_count = 0;
    foreach ($header as $header_cell) {
      if (is_array($header_cell)) {
        $header_count += isset($header_cell['colspan']) ? $header_cell['colspan'] : 1;
      } else {
        $header_count++;
      }
    }
    $rows[] = array(array('data' => $empty, 'colspan' => $header_count, 'class' => array('empty', 'message')));
  }

  // Format the table header:
  if (count($header)) {
    $ts = tablesort_init($header);
    // HTML requires that the thead tag has tr tags in it followed by tbody
    // tags. Using ternary operator to check and see if we have any rows.
    $output .= (count($rows) ? ' <thead><tr>' : ' <tr>');
    foreach ($header as $cell) {
      $cell = tablesort_header($cell, $header, $ts);
      $output .= _theme_table_cell($cell, TRUE);
    }
    // Using ternary operator to close the tags based on whether or not there are rows
    $output .= (count($rows) ? " </tr></thead>\n" : "</tr>\n");
  } else {
    $ts = array();
  }

  // Format the table rows:
  if (count($rows)) {
    $output .= "<tbody>\n";
    $flip = array('even' => 'odd', 'odd' => 'even');
    $class = 'even';
    foreach ($rows as $number => $row) {
      $attributes = array();

      // Check if we're dealing with a simple or complex row
      if (isset($row['data'])) {
        foreach ($row as $key => $value) {
          if ($key == 'data') {
            $cells = $value;
          } else {
            $attributes[$key] = $value;
          }
        }
      } else {
        $cells = $row;
      }
      if (count($cells)) {
        // Add odd/even class
        if (empty($row['no_striping'])) {
          $class = $flip[$class];
          $attributes['class'][] = $class;
        }

        // Build row
        $output .= ' <tr' . drupal_attributes($attributes) . '>';
        $i = 0;
        foreach ($cells as $cell) {
          $cell = tablesort_cell($cell, $header, $ts, $i++);
          $output .= _theme_table_cell($cell);
        }
        $output .= " </tr>\n";
      }
    }
    $output .= "</tbody>\n";
  }

  $output .= "</table>\n";
  return $output;
}

function nevia_status_messages(&$variables) {
  $display = $variables['display'];
  $output = '';

  $message_info = array(
      'status' => array(
          'heading' => 'Status message',
          'class' => 'success',
      ),
      'error' => array(
          'heading' => 'Error message',
          'class' => 'error',
      ),
      'warning' => array(
          'heading' => 'Warning message',
          'class' => '',
      ),
  );

  foreach (drupal_get_messages($display) as $type => $messages) {
	  $class_info = '';
	  if(!empty($message_info[$type]['class'])){
	  	$class_info =$message_info[$type]['class'];
	  }
    $message_class = $type != 'warning' ? $class_info : 'warning';
    $output .= "<div class=\"notification alert alert-block alert-$message_class $message_class closeable fade in\">\n";
    if (!empty($message_info[$type]['heading'])) {
      $output .= '<h2 class="element-invisible">' . $message_info[$type]['heading'] . "</h2>\n";
    }
    if (count($messages) > 1) {
      $output .= " <ul>\n";
      foreach ($messages as $message) {
        $output .= '  <li>' . $message . "</li>\n";
      }
      $output .= " </ul>\n";
    } else {
      $output .= $messages[0];
    }
    $output .= "</div>\n";
  }
  return $output;
}

function nevia_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.
  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
          'class' => array('pager-first'),
          'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
          'class' => array('pager-previous'),
          'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
            'class' => array('pager-ellipsis'),
            'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
              'class' => array('pager-item'),
              'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
              'class' => array('pager-current'),
              'data' => '<a class="current">' . $i . '</a>',
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
              'class' => array('pager-item'),
              'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
            'class' => array('pager-ellipsis'),
            'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
          'class' => array('pager-next'),
          'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
          'class' => array('pager-last'),
          'data' => $li_last,
      );
    }
    return '<div class="pagination"><h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
                'items' => $items,
                'attributes' => array('class' => array('pager')),
            )) . '</div>';
  }
}

function nevia_preprocess_node(&$variables) {
  $variables['view_mode'] = $variables['elements']['#view_mode'];
  // Provide a distinct $teaser boolean.
  $variables['teaser'] = $variables['view_mode'] == 'teaser';
  $variables['node'] = $variables['elements']['#node'];
  $node = $variables['node'];

  $variables['date'] = format_date($node->created);
  $variables['name'] = theme('username', array('account' => $node));

  $uri = entity_uri('node', $node);
  $variables['node_url'] = url($uri['path'], $uri['options']);
  $variables['title'] = check_plain($node->title);
  $variables['page'] = $variables['view_mode'] == 'full' && node_is_page($node);

  // Flatten the node object's member fields.
  $variables = array_merge((array) $node, $variables);

  // Helpful $content variable for templates.
  $variables += array('content' => array());
  foreach (element_children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Make the field variables available with the appropriate language.
  field_attach_preprocess('node', $node, $variables['content'], $variables);

  // Display post information only on certain node types.
  if (variable_get('node_submitted_' . $node->type, TRUE)) {
    $variables['display_submitted'] = TRUE;
    $submitted = '<span><i class="halflings user"></i>' . t('by') . ' ' . $variables['name'] . '</span>';

    if (!empty($variables['content']['field_tags'])) {
      $submitted .= '<span><i class="halflings tag"></i>' . nevia_format_comma_field('field_tags', $node) . '</span>';
    }
    if (!empty($node->comment_count)) {
      $submitted .= '<span><i class="halflings comments"></i>' . t('With') . ' <a href="' . url('node/' . $node->nid) . '">' . $node->comment_count . ' ' . t('Comments') . '</a></span>';
    }
    $variables['submitted'] = $submitted; //t('Submitted by !username on !datetime', array('!username' => $variables['name'], '!datetime' => $variables['date']));
    $variables['user_picture'] = theme_get_setting('toggle_node_user_picture') ? theme('user_picture', array('account' => $node)) : '';
  } else {
    $variables['display_submitted'] = FALSE;
    $variables['submitted'] = '';
    $variables['user_picture'] = '';
  }

  // Gather node classes.
  $variables['classes_array'][] = drupal_html_class('node-' . $node->type);
  if ($variables['promote']) {
    $variables['classes_array'][] = 'node-promoted';
  }
  if ($variables['sticky']) {
    $variables['classes_array'][] = 'node-sticky';
  }
  if (!$variables['status']) {
    $variables['classes_array'][] = 'node-unpublished';
  }
  if ($variables['teaser']) {
    $variables['classes_array'][] = 'node-teaser';
  }
  if (isset($variables['preview'])) {
    $variables['classes_array'][] = 'node-preview';
  }

  // Clean up name so there are no underscores.
  $variables['theme_hook_suggestions'][] = 'node__' . $node->type;
  $variables['theme_hook_suggestions'][] = 'node__' . $node->nid;
}

function nevia_tagclouds_weighted(array $vars) {
  $terms = $vars['terms'];

  $output = '<div class="tags">';
  $display = variable_get('tagclouds_display_type', 'style');

  if (module_exists('i18n_taxonomy')) {
    $language = i18n_language();
  }

  if ($display == 'style') {
    foreach ($terms as $term) {
      if (module_exists('i18n_taxonomy')) {
        $term_name = i18n_taxonomy_term_name($term, $language->language);
        $term_desc = tagclouds_i18n_taxonomy_term_description($term, $language->language);
      } else {
        $term_name = $term->name;
        $term_desc = $term->description;
      }
      $output .= tagclouds_display_term_link_weight($term_name, $term->tid, $term->weight, $term_desc);
    }
  } else {
    foreach ($terms as $term) {
      if (module_exists('i18n_taxonomy')) {
        $term_name = i18n_taxonomy_term_name($term, $language->language);
        $term_desc = tagclouds_i18n_taxonomy_term_description($term, $language->language);
      } else {
        $term_name = $term->name;
        $term_desc = $term->description;
      }
      if ($term->count == 1 && variable_get("tagclouds_display_node_link", false)) {
        $output .= tagclouds_display_node_link_count($term_name, $term->tid, $term->nid, $term->count, $term_desc);
      } else {
        $output .= tagclouds_display_term_link_count($term_name, $term->tid, $term->count, $term_desc);
      }
    }
  }

  $output .='</div>';
  return $output;
}

function nevia_superfish_menu_item($variables) {
  $element = $variables['element'];
  $properties = $variables['properties'];
  $sub_menu = '';

  if ($element['below']) {
    $sub_menu .= isset($variables['wrapper']['wul'][0]) ? $variables['wrapper']['wul'][0] : '';
    $sub_menu .= ($properties['megamenu']['megamenu_content']) ? '<ol>' : '<ul>';
    $sub_menu .= $element['below'];
    $sub_menu .= ($properties['megamenu']['megamenu_content']) ? '</ol>' : '</ul>';
    $sub_menu .= isset($variables['wrapper']['wul'][1]) ? $variables['wrapper']['wul'][1] : '';
  }




  if (isset($element['localized_options']['attributes']['class'])) {
    foreach ($element['localized_options']['attributes']['class'] as $key => $class) {


      if (substr($class, 0, 9) == 'halflings') {


        // We're injecting custom HTML into the link text, so if the original
        // link text was not set to allow HTML (the usual case for menu items),
        // we MUST do our own filtering of the original text with check_plain(),
        // then specify that the link text has HTML content.
        if (!isset($element['localized_options']['options']['html']) || empty($element['localized_options']['options']['html'])) {
          $element['item']['link']['title'] = check_plain($element['item']['link']['title']);
          $element['localized_options']['html'] = TRUE;
        }


        // Create additional HTML markup for the link's icon element and wrap
        // the link text in a SPAN element, to easily turn it on or off via CSS.
        $class = implode(' ', $element['localized_options']['attributes']['class']);
        $element['item']['link']['title'] = '<i class="' . $class . '"></i> <span>' . $element['item']['link']['title'] . '</span>';

        // Finally, remove the icon class from link options, so it is not
        // printed twice.
        unset($element['localized_options']['attributes']['class'][$key]);
      }
    }
  }



  $output = '<li' . drupal_attributes($element['attributes']) . '>';
  $output .= ($properties['megamenu']['megamenu_column']) ? '<div class="sf-megamenu-column">' : '';
  $output .= isset($properties['wrapper']['whl'][0]) ? $properties['wrapper']['whl'][0] : '';
  if ($properties['use_link_theme']) {
    $link_variables = array(
        'menu_item' => $element['item'],
        'link_options' => $element['localized_options']
    );
    $output .= theme('superfish_menu_item_link', $link_variables);
  } else {
    $output .= l($element['item']['link']['title'], $element['item']['link']['href'], $element['localized_options']);
  }
  $output .= isset($properties['wrapper']['whl'][1]) ? $properties['wrapper']['whl'][1] : '';
  $output .= ($properties['megamenu']['megamenu_wrapper']) ? '<ul class="sf-megamenu"><li class="sf-megamenu-wrapper ' . $element['attributes']['class'] . '">' : '';
  $output .= $sub_menu;
  $output .= ($properties['megamenu']['megamenu_wrapper']) ? '</li></ul>' : '';
  $output .= ($properties['megamenu']['megamenu_column']) ? '</div>' : '';
  $output .= '</li>';

  return $output;
}

function nevia_menu_link(array $variables) {


  $element = $variables['element'];
  $sub_menu = '';



  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }

  if (isset($element['#original_link']['options']['attributes']['class'])) {
    foreach ($element['#original_link']['options']['attributes']['class'] as $key => $class) {

      if (substr($class, 0, 9) == 'halflings') {


        // Finally, remove the icon class from link options, so it is not
        // printed twice.
        unset($element['#localized_options']['attributes']['class'][$key]);

        //dpm($element); // For debugging.
      }
    }
  }


  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

function nevia_link($variables) {

  $classes = array();
  if (!empty($variables['options']['attributes']['class'])) {
    $classes = $variables['options']['attributes']['class'];
    
    if (count($classes) > 1) {
      foreach ($classes as $key => $class) {
        if (substr($class, 0, 9) == 'halflings') {
          unset($variables['options']['attributes']['class'][$key]);
        }
      }
    }
  }
  return '<a href="' . check_plain(url($variables['path'], $variables['options'])) . '"' . drupal_attributes($variables['options']['attributes']) . '>' . ($variables['options']['html'] ? $variables['text'] : check_plain($variables['text'])) . '</a>';
}
// Display message when cart is empty
function nevia_uc_empty_cart() {
  $cart_is_empty_message = '<p class="uc-cart-empty">' . t('There are no products in your shopping cart. ') .'<a href="/content/store">'.t('Visit our Store pages here.') .'</a></p>';
  return $cart_is_empty_message;
}
// Prompt user when email to reset password email is sent
function nevia_form_alter(&$form, &$form_state, $form_id) { 
  if ($form_id =='user_pass'){
      $form['#submit'][] = 'nevia_password_reset_prompt';
   }
 }

 function nevia_password_reset_prompt(&$form, &$form_state){
   $_SESSION['messages'] = '';
   drupal_set_message("Email with password reset instructions was sent to your mailbox");
} 
/**
 * [test_innovators_main_menu description]
 * @param  array $tree main menu tree to loop over
 * @return string       rendered main menu html
 */
function test_innovators_header($main_menu, $user_menu, $logos){
  $main_menu_html = '';
  $user_menu_html = '';
  //check if menu is visible by user
  if(!empty($main_menu)){
    //set main menu logo
    $main_logo_path = isset($logos['header']) ? $logos['header']['logo_path'] : '/misc/druplicon.png'; 
    $main_logo_href = isset($logos['header']) ? $logos['header']['logo_url'] : '/'; 
    $main_menu_html .= '<div class="navbar-header">';
    $main_menu_html .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">';
    $main_menu_html .= '<span class="sr-only">Toggle navigation</span>';
    $main_menu_html .= '<div class="burger-menu"><i class="fa fa-bars fa-2x" aria-hidden="true"></i></div>';
    $main_menu_html .= '</button>';
    $main_menu_html .= '</div>';
    $main_menu_html .= '<div id="main-nav">';
    $main_menu_html .= '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">';
    $main_menu_html .= '<ul class="nav navbar-nav navbar-right">';
    $main_menu_html .= build_li_list($main_menu);
    $main_menu_html .= '</ul>';
    $main_menu_html .= '</div>';
    $main_menu_html .= '</div>';
  }
  //check if menu is visible by user
  if(!empty($user_menu)){
    $user_menu_html .=  '<div class="container inov-nav">';
    $user_menu_html .= '<ul id="logged-nav">';
    $user_menu_html .= build_li_list($user_menu);
    $user_menu_html .= '</ul>';
    $user_menu_html .= '</div>';
  }
  $header =   '<nav class="navbar">';
  $header .= '<a class="navbar-brand" id="navbar-brand" href="' . $main_logo_href . '">';
  $header .= '<img src="' . $main_logo_path . '">';
  $header .= '</a>';
  $header .= $user_menu_html;
  $header .= $main_menu_html;
  $header .= '</nav>';
  return $header;
}

function build_li_list($menu_array){
  $li_list = '';
  foreach ($menu_array as $link) { //itterate over all first level menu links
    $ul_below = '';
    $li_attr = '';
    $a_attr = '';
    $caret = '';
    $a_href = 'href="/'.$link['link']['link_path'].'"';
    $a = '<a ';
    if(!empty($link['below'])){
      $li_attr = ' class="dropdown"';
      $a_class = ' class="dropdown-toggle disabled';
      $a_attr = 'data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';
      $caret = '<span class="caret';
      // caret disabled when expand on mobile is not marked
      if($link['link']['options']['attributes']['expand_on_mobile']){
        $caret .= ' expends-OM';
        $a_class .= ' expand-OM';
        $a_href = 'href="#"';
      }
      $a_class .= '"';
      $caret .= '"><span>';
      $ul_below = '<ul class="dropdown-menu">';
      $ul_below .= build_li_list($link['below']);
      $ul_below .= '</ul>';
      $a_attr .= $a_class;
    }
    //build li
    $a .= $a_href . $a_attr . '>'. $link['link']['link_title'] . $caret . '</a>';
    $li = '<li' . $li_attr . '>' . $a . '</a>' . $ul_below . '</li>';
    if($link['link']['options']['attributes']['info_link']){ //info_link overides all other attr
      $li = '<li class="info-link">' . $link['link']['link_title'] . '</li>';
    }
    if($link['link']['link_title'] == 'cart'){ // if title of link is cart then change title to cart icon
      $li = '<li><a href="' . $link['link']['link_path'] . '"><i class="fa fa-shopping-cart"></i></a></li>';
    }
    $li_list .= $li;
  }
  return $li_list;
}