<?php

abstract class EPViewAction extends FormlessAction {
	
	public function onView() {
		
	}
	
	/**
	 * Show a message in an error box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showError( Message $message ) {
		$this->getOutput()->addHTML(
			'<p class="visualClear errorbox">' . $message->parse() . '</p>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Show a message in a warning box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showWarning( Message $message ) {
		$this->getOutput()->addHTML(
			'<p class="visualClear warningbox">' . $message->parse() . '</p>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Show a message in a success box.
	 *
	 * @since 0.1
	 *
	 * @param Message $message
	 */
	protected function showSuccess( Message $message ) {
		$this->getOutput()->addHTML(
			'<div class="successbox"><strong><p>' . $message->parse() . '</p></strong></div>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}
	
	/**
	 * Adds a navigation menu with the provided links.
	 * Links should be provided in an array with:
	 * label => Title (object)
	 *
	 * @since 0.1
	 *
	 * @param array $items
	 */
	protected function displayNavigation( array $items = array() ) {
		$links = array();
		$items = array_merge( $this->getDefaultNavigationItems(), $items );

		foreach ( $items as $label => $data ) {
			if ( is_array( $data ) ) {
				$target = array_shift( $data );
				$attribs = $data;
			}
			else {
				$target = $data;
				$attribs = array();
			}

			$links[] = Linker::linkKnown(
				$target,
				htmlspecialchars( $label ),
				$attribs
			);
		}

		$this->getOutput()->addHTML(
			Html::rawElement( 'p', array(), $this->getLanguage()->pipeList( $links ) )
		);
	}

	/**
	 * Returns the default nav items for @see displayNavigation.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected function getDefaultNavigationItems() {
		$items = array(
			wfMsg( 'ep-nav-orgs' ) => SpecialPage::getTitleFor( 'Institutions' ),
			wfMsg( 'ep-nav-courses' ) => SpecialPage::getTitleFor( 'Courses' ),
		);

		$items[wfMsg( 'ep-nav-students' )] = SpecialPage::getTitleFor( 'Students' );

		if ( EPStudent::has( array( 'user_id' => $this->getUser()->getId() ) ) ) {
			$items[wfMsg( 'ep-nav-mycourses' )] = SpecialPage::getTitleFor( 'MyCourses' );
		}

		return $items;
	}

	/**
	 * Display the summary data.
	 *
	 * @since 0.1
	 *
	 * @param EPDBObject $item
	 * @param boolean $collapsed
	 * @param array $summaryData
	 */
	protected function displaySummary( EPDBObject $item, $collapsed = false, array $summaryData = null ) {
		$out = $this->getOutput();

		$class = 'wikitable ep-summary mw-collapsible';

		if ( $collapsed ) {
			$class .= ' mw-collapsed';
		}

		$out->addHTML( Html::openElement( 'table', array( 'class' => $class ) ) );

		$out->addHTML( '<tr>' . Html::element( 'th', array( 'colspan' => 2 ), wfMsg( 'ep-item-summary' ) ) . '</tr>' );

		$summaryData = is_null( $summaryData ) ? $this->getSummaryData( $item ) : $summaryData;

		foreach ( $summaryData as $stat => $value ) {
			$out->addHTML( '<tr>' );

			$out->addHTML( Html::element(
				'th',
				array( 'class' => 'ep-summary-name' ),
				wfMsg( strtolower( get_called_class() ) . '-summary-' . $stat )
			) );

			$out->addHTML( Html::rawElement(
				'td',
				array( 'class' => 'ep-summary-value' ),
				$value
			) );

			$out->addHTML( '</tr>' );
		}

		$out->addHTML( Html::closeElement( 'table' ) );
	}

	/**
	 * Gets the summary data.
	 * Returned values must be escaped.
	 *
	 * @since 0.1
	 *
	 * @param EPDBObject $item
	 *
	 * @return array
	 */
	protected function getSummaryData( EPDBObject $item ) {
		return array();
	}
	
}