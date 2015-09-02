<?php

/**
 * Get US States
 * @version 1.0
 */
function wak_get_states() {

	return array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DC' => 'D.C.',
		'DE' => 'Delaware',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming'
	);

}

/**
 * US State Dropdown
 * @version 1.0
 */
function wak_states_dropdown( $name = '', $id = '', $none = '', $selected = '' ) {

	$states = wak_get_states();

	$output = '<select class="form-control" name="' . $name . '" id="' . $id . '">';

	if ( $none != '' ) {

		$output .= '<option value=""';
		if ( $selected == '' ) $output .= ' selected="selected"';
		$output .= '>' . $none . '</option>';

	}

	foreach ( $states as $state => $label ) {

		$output .= '<option value="' . $state . '"';
		if ( $selected == $state ) $output .= ' selected="selected"';
		$output .= '>' . $label . '</option>';

	}

	$output .= '</select>';

	return $output;

}

?>