<?php

/**
 * @file
 * template.php
 */

/**
 * Implements hook_html_head_alter().
 */
function stay_smart_2017_html_head_alter(&$head_elements) {
  // Mobile Viewport.
  $head_elements['viewport'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'),
  );
  // IE Latest Browser.
  $head_elements['ie_view'] = array(
    '#type' => 'html_tag',
    '#tag' => 'meta',
    '#attributes' => array('http-equiv' => 'x-ua-compatible', 'content' => 'ie=edge'),
  );
}

/**
 * Implements hook_js_alter().
 */
function stay_smart_2017_js_alter(&$javascript) {
  $javascript['misc/jquery.js']['data'] = drupal_get_path('theme', 'stay_smart_2017') . '/vendor/jquery/jquery-3.1.1.min.js';
}

/**
 * Implements hook_preprocess_html().
 */
function stay_smart_2017_preprocess_html(&$variables) {
  drupal_add_js("(function(h) {h.className = h.className.replace('no-js', '') })(document.documentElement);", array('type' => 'inline', 'scope' => 'header'));
  drupal_add_js('jQuery.extend(Drupal.settings, { "pathToTheme": "' . path_to_theme() . '" });', 'inline');
  // Drupal forms.js does not support new jQuery. Migrate library needed.
  drupal_add_js(drupal_get_path('theme', 'stay_smart_2017') . '/vendor/jquery/jquery-migrate-1.2.1.min.js', array('weight' => -1));
  drupal_add_js(drupal_get_path('theme', 'stay_smart_2017') . '/vendor/jquery/jquery.size.js', array('group' => 'JS_LIBRARY', 'weight' => -1));
}

/**
 * Implements hook_preprocess_page().
 */
function stay_smart_2017_preprocess_page(&$variables) {
  $logo_mobile = '';
  if ($logo_mobile_fid = theme_get_setting('stay_smart_2017_mobile_logo')) {
    $logo_mobile_file = file_load($logo_mobile_fid);
    $logo_mobile_alt = theme_get_setting('stay_smart_2017_mobile_logo_alt');
    $logo_mobile = theme_image(array(
      'path' => file_create_url($logo_mobile_file->uri),
      'alt' => $logo_mobile_alt,
      'attributes' => array('class' => array('header__logo_mobile-image')),
    ));
    $logo_mobile = l($logo_mobile, $variables['front_page'], array(
      'html' => TRUE,
      'attributes' => array(
        'id' => 'logo-mobile',
        'title' => t('Back to Homepage'),
        'rel' => 'home',
      ),
    ));
  }

  $variables['logo_mobile'] = $logo_mobile;

  if (!empty($variables['node'])) {
    $node = $variables['node'];
    if (isset($node->field_hide_sidebar_nav)) {
      try {
        $wrapped_entity = entity_metadata_wrapper('node', $node);
        if ($wrapped_entity->field_hide_sidebar_nav->value()) {
          $variables['page']['sidebar_first'] = array();
        }
      }
      catch (Exception $e) {
        watchdog_exception('stay_smart_2017', $e);
      }
    }
  }
}

/**
 * Implements hook_preprocess_field().
 */
function stay_smart_2017_preprocess_field(&$variables) {
  // Bean 'Image and Text' field 'Link To' to show 'Read [title]' text.
  if ($variables['element']['#field_name'] === 'field_link_to' && $variables['element']['#bundle'] === 'image_and_text') {
    if (!empty($variables['items'][0]) && !empty($variables['element']['#object']->title)) {
      // This only applies if field has a non-configurable title.
      if ($variables['items'][0]['#field']['settings']['title'] === 'none') {
        $variables['items'][0]['#element']['title'] = t('Read !title', array('!title' => $variables['element']['#object']->title));
      }
    }
  }
}

/**
 * Implements hook_preprocess_node().
 */
function stay_smart_2017_preprocess_node(&$variables) {
  if ($variables['view_mode'] === 'teaser' || $variables['view_mode'] === 'compact') {
    // Apply thumbnail class to node teaser view if image exists.
    $has_thumb = !empty($variables['content']['field_thumbnail']);
    $has_image = !empty($variables['content']['field_image']);
    $has_featured_image = !empty($variables['content']['field_feature_image']);
    if ($has_thumb || $has_image || $has_featured_image) {
      $variables['classes_array'][] = 'has-thumbnail';
    }
  }

  if ($variables['type'] === 'webform') {
    // Hide submitted date on webforms.
    $variables['display_submitted'] = FALSE;
  }

  if ($variables['type'] === 'alert') {
    $priority = $variables['elements']['field_priority_level'][0]['#markup'];
    $variables['classes_array'][] = 'priority-level-' . strtolower($priority);
  }

}

/**
 * Implements template_preprocess_block().
 */
function stay_smart_2017_preprocess_block(&$variables) {
  if ($variables['block_html_id'] == 'block-menu-block-2') {
    $child_content = array();
    foreach ($variables['elements']['#content'] as $key => $content) {
      if (is_numeric($key)) {
        if ($node = node_load(str_replace('node/', '', $content['#href']))) {
          $child_content[] = node_view($node, 'landing_page_teaser');
        }
      }
    }
    $variables['children_contents'] = $child_content;
  }
}

/**
 * Implements theme_breadcrumb().
 */
function stay_smart_2017_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $output = '';

  if (!empty($breadcrumb)) {
    // Build the breadcrumb trail.
    $output = '<nav class="breadcrumbs--inverted" role="navigation" aria-label="breadcrumb">';
    $output .= '<ul><li>' . implode('</li><li>', $breadcrumb) . '</li></ul>';
    $output .= '</nav>';
  }

  return $output;
}

/**
 * Implements hook_form_alter().
 */
function stay_smart_2017_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id === 'search_api_page_search_form_default_search') {
    // Global header form.
    $form['keys_1']['#attributes']['placeholder'] = t('Type search term here');
    $form['keys_1']['#title'] = t('Search field');
  }
  elseif ($form_id === 'search_api_page_search_form') {
    // Search page (above results) form.
    $form['form']['keys_1']['#title'] = t('Type search term here');
  }
  if ($form_id === 'search_form') {
    // Search form on page not found (404 page).
    $form['basic']['keys']['#title'] = t('Type search term here');
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function stay_smart_2017_form_search_api_page_search_form_solr_search_alter(&$form, &$form_state) {
  if (!empty($form['keys_6'])) {
    $form['keys_6']['#attributes']['placeholder'] = t('Search for a service or ask a question');
  }
}

/**
 * Implements theme_preprocess_search_api_page_result().
 */
function stay_smart_2017_preprocess_search_api_page_result(&$variables) {
  // Strip out HTML tags from search results.
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display.
  $variables['info'] = '';
}

/**
 * Implements theme_preprocess_search_result().
 */
function stay_smart_2017_preprocess_search_result(&$variables) {
  // Strip out HTML tags from search results (404 page).
  $variables['snippet'] = strip_tags($variables['snippet']);
  // Remove the author / date from the result display (404 page).
  $variables['info'] = '';
}
