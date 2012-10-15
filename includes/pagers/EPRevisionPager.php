<?php

/**
 * History pager.
 *
 * @since 0.1
 *
 * @file EPRevisionPager.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPRevisionPager extends ReverseChronologicalPager {
	/**
	 * Context in which this pager is being shown.
	 * @since 0.1
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @since 0.1
	 * @var EPPageTable
	 */
	protected $table;

	protected $rowNr = 0;

	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param EPPageTable $table
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, EPPageTable $table, array $conds = array() ) {
		if ( method_exists( 'ReverseChronologicalPager', 'getUser' ) ) {
			parent::__construct( $context );
		}
		else {
			parent::__construct();
		}

		$this->conds = $conds;
		$this->context = $context;
		$this->table = $table;

		$this->mDefaultDirection = true;
		$this->getDateCond(
			$context->getRequest()->getText( 'year', '' ),
			$context->getRequest()->getText( 'month', '' )
		);
	}

	/**
	 * @see parent::getStartBody
	 * @since 0.1
	 */
	function getStartBody() {
		return '<ul>';
	}

	/**
	 * Abstract formatting function. This should return an HTML string
	 * representing the result row $row. Rows will be concatenated and
	 * returned by getBody()
	 *
	 * @param $row Object: database row
	 *
	 * @return String
	 */
	function formatRow( $row ) {
		$revision = EPRevisions::singleton()->newRowFromDBResult( $row );
		$object = $revision->getObject();

		$html = '';

		$html .= $object->getLink(
			'view',
			htmlspecialchars( $this->getLanguage()->timeanddate( $revision->getField( 'time' ) ) ),
			array(),
			array( 'revid' => $revision->getId() )
		);

		$html .= '&#160;&#160;';

		$html .= Linker::userLink( $revision->getUser()->getId(), $revision->getUser()->getName() )
			. Linker::userToolLinks( $revision->getUser()->getId(), $revision->getUser()->getName() );

		if ( $revision->getField( 'minor_edit' ) ) {
			$html .= '&#160;&#160;';
			$html .= '<strong>' . $this->msg( 'minoreditletter' )->escaped() . '</strong>';
		}

		if ( $revision->getField( 'comment' ) !== '' ) {
			$html .= '&#160;.&#160;.&#160;';

			$html .= Html::rawElement(
				'span',
				array(
					'dir' => 'auto',
					'class' => 'comment',
				),
				'(' . $this->getOutput()->parseInline( $revision->getField( 'comment' ) ) . ')'
			);
		}

		if ( $this->getUser()->isAllowed( $this->table->getEditRight() ) ) {
			$actionLinks = array();

			if ( $this->mOffset !== '' || $this->rowNr < $this->mResult->numRows() - 1 ) {
				$actionLinks[] = $object->getLink(
					'epundo',
					$this->msg( 'ep-revision-undo' )->escaped(),
					array(),
					array( 'revid' => $revision->getId() )
				);
			}

			if ( $this->mOffset !== '' || $this->rowNr != 0 ) {
				$actionLinks[] = $object->getLink(
					'eprestore',
					$this->msg( 'ep-revision-restore' )->escaped(),
					array(),
					array( 'revid' => $revision->getId() )
				);
			}

			if ( !empty( $actionLinks ) ) {
				$html .= '&#160;.&#160;.&#160;';
				$html .= '(' .  $this->getLanguage()->pipeList( $actionLinks ) . ')';
			}
		}

		$this->rowNr++;

		return '<li>' . $html . '</li>';
	}

	/**
	 * @see parent::getEndBody
	 * @since 0.1
	 */
	function getEndBody() {
		return '</ul>';
	}

	/**
	 * This function should be overridden to provide all parameters
	 * needed for the main paged query. It returns an associative
	 * array with the following elements:
	 *	tables => Table(s) for passing to Database::select()
	 *	fields => Field(s) for passing to Database::select(), may be *
	 *	conds => WHERE conditions
	 *	options => option array
	 *	join_conds => JOIN conditions
	 *
	 * @return Array
	 */
	function getQueryInfo() {
		$table = EPRevisions::singleton();
		return array(
			'tables' => $table->getName(),
			'fields' => $table->getPrefixedFields( $table->getFieldNames() ),
			'conds' => $table->getPrefixedValues( $this->conds ),
			'options' => array( 'USE INDEX' => array( $table->getName() => $table->getPrefixedField( 'time' ) ) ),
		);
	}

	/**
	 * This function should be overridden to return the name of the index fi-
	 * eld.  If the pager supports multiple orders, it may return an array of
	 * 'querykey' => 'indexfield' pairs, so that a request with &count=querykey
	 * will use indexfield to sort.  In this case, the first returned key is
	 * the default.
	 *
	 * Needless to say, it's really not a good idea to use a non-unique index
	 * for this!  That won't page right.
	 *
	 * @return string|Array
	 */
	function getIndexField() {
		return EPRevisions::singleton()->getPrefixedField( 'time' );
	}
}
