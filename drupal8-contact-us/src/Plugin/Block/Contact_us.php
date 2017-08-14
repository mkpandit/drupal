<?php

namespace Drupal\contact_us\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Contact Us Block
 *
 * @Block(
 *   id = "drupal_contact_us",
 *   admin_label = @Translation("Drupal 8 Contact Us Block"),
 * )
 */
 
 
class Contact_us extends BlockBase {
  /**
   * {@inheritdoc}
   */
   
	public function build() {
		$form = \Drupal::formBuilder()->getForm('Drupal\contact_us\Form\Contact_us');
		return $form;
	}
}
