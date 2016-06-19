<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2016
 * @package Admin
 * @subpackage JsonAdm
 */


namespace Aimeos\Admin\JsonAdm;


/**
 * JSON API common client
 *
 * @package Admin
 * @subpackage JsonAdm
 */
class Base
{
	private $view;
	private $context;
	private $templatePaths;
	private $path;


	/**
	 * Initializes the client
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context MShop context object
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $templatePaths List of file system paths where the templates are stored
	 * @param string $path Name of the client separated by slashes, e.g "product/stock"
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context, \Aimeos\MW\View\Iface $view, array $templatePaths, $path )
	{
		$this->view = $view;
		$this->context = $context;
		$this->templatePaths = $templatePaths;
		$this->path = $path;
	}


	/**
	 * Deletes the resource or the resource list
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function delete( $body, array &$header, &$status )
	{
		$header = array( 'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"' );
		$view = $this->getView();

		try
		{
			$view = $this->deleteItems( $view, $body );
			$status = 200;
		}
		catch( \Aimeos\Admin\JsonAdm\Exception $e )
		{
			$status = $e->getCode();
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'admin/jsonadm', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** admin/jsonadm/standard/template-delete
		 * Relative path to the JSON API template for DELETE requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the DELETE method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-get
		 * @see admin/jsonadm/standard/template-patch
		 * @see admin/jsonadm/standard/template-post
		 * @see admin/jsonadm/standard/template-put
		 * @see admin/jsonadm/standard/template-options
		 */
		$tplconf = 'admin/jsonadm/standard/template-delete';
		$default = 'delete-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the requested resource or the resource list
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function get( $body, array &$header, &$status )
	{
		$header = array( 'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"' );
		$view = $this->getView();

		try
		{
			$view = $this->getItem( $view );
			$status = 200;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** admin/jsonadm/standard/template-get
		 * Relative path to the JSON API template for GET requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the GET method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-delete
		 * @see admin/jsonadm/standard/template-patch
		 * @see admin/jsonadm/standard/template-post
		 * @see admin/jsonadm/standard/template-put
		 * @see admin/jsonadm/standard/template-options
		 */
		$tplconf = 'admin/jsonadm/standard/template-get';
		$default = 'get-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Updates the resource or the resource list partitially
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function patch( $body, array &$header, &$status )
	{
		$header = array( 'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"' );
		$view = $this->getView();

		try
		{
			$view = $this->patchItems( $view, $body, $header );
			$status = 200;
		}
		catch( \Aimeos\Admin\JsonAdm\Exception $e )
		{
			$status = $e->getCode();
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'admin/jsonadm', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** admin/jsonadm/standard/template-patch
		 * Relative path to the JSON API template for PATCH requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the PATCH method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-get
		 * @see admin/jsonadm/standard/template-post
		 * @see admin/jsonadm/standard/template-delete
		 * @see admin/jsonadm/standard/template-put
		 * @see admin/jsonadm/standard/template-options
		 */
		$tplconf = 'admin/jsonadm/standard/template-patch';
		$default = 'patch-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Creates or updates the resource or the resource list
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function post( $body, array &$header, &$status )
	{
		$header = array( 'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"' );
		$view = $this->getView();

		try
		{
			$view = $this->postItems( $view, $body, $header );
			$status = 201;
		}
		catch( \Aimeos\Admin\JsonAdm\Exception $e )
		{
			$status = $e->getCode();
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'admin/jsonadm', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** admin/jsonadm/standard/template-post
		 * Relative path to the JSON API template for POST requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the POST method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-get
		 * @see admin/jsonadm/standard/template-patch
		 * @see admin/jsonadm/standard/template-delete
		 * @see admin/jsonadm/standard/template-put
		 * @see admin/jsonadm/standard/template-options
		 */
		$tplconf = 'admin/jsonadm/standard/template-post';
		$default = 'post-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Creates or updates the resource or the resource list
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function put( $body, array &$header, &$status )
	{
		$status = 501;
		$header = array( 'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"' );
		$view = $this->getView();

		$view->errors = array( array(
			'title' => $this->getContext()->getI18n()->dt( 'admin/jsonadm', 'Not implemented, use PATCH instead' ),
		) );

		/** admin/jsonadm/standard/template-put
		 * Relative path to the JSON API template for PUT requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the PUT method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-delete
		 * @see admin/jsonadm/standard/template-patch
		 * @see admin/jsonadm/standard/template-post
		 * @see admin/jsonadm/standard/template-get
		 * @see admin/jsonadm/standard/template-options
		 */
		$tplconf = 'admin/jsonadm/standard/template-put';
		$default = 'put-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the available REST verbs and the available resources
	 *
	 * @param string $body Request body
	 * @param array &$header Variable which contains the HTTP headers and the new ones afterwards
	 * @param integer &$status Variable which contains the HTTP status afterwards
	 * @return string Content for response body
	 */
	public function options( $body, array &$header, &$status )
	{
		$context = $this->getContext();
		$view = $this->getView();

		try
		{
			$resources = $attributes = array();

			foreach( $this->getDomains( $view ) as $domain )
			{
				$manager = \Aimeos\MShop\Factory::createManager( $context, $domain );
				$resources = array_merge( $resources, $manager->getResourceType( true ) );
				$attributes = array_merge( $attributes, $manager->getSearchAttributes( true ) );
			}

			$view->resources = $resources;
			$view->attributes = $attributes;

			$header = array(
				'Content-Type' => 'application/vnd.api+json; supported-ext="bulk"',
				'Allow' => 'DELETE,GET,POST,OPTIONS'
			);
			$status = 200;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $context->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** admin/jsonadm/standard/template-options
		 * Relative path to the JSON API template for OPTIONS requests
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jsonadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the OPTIONS method of the JSON API
		 * @since 2015.12
		 * @category Developer
		 * @see admin/jsonadm/standard/template-delete
		 * @see admin/jsonadm/standard/template-patch
		 * @see admin/jsonadm/standard/template-post
		 * @see admin/jsonadm/standard/template-get
		 * @see admin/jsonadm/standard/template-put
		 */
		$tplconf = 'admin/jsonadm/standard/template-options';
		$default = 'options-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Deletes one or more items
	 *
	 * @param \Aimeos\MW\View\Iface $view View instance with "param" view helper
	 * @param string $body Request body
	 * @return \Aimeos\MW\View\Iface $view View object that will contain the "total" property afterwards
	 * @throws \Aimeos\Admin\JsonAdm\Exception If the request body is invalid
	 */
	protected function deleteItems( \Aimeos\MW\View\Iface $view, $body )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), $this->getPath() );

		if( ( $id = $view->param( 'id' ) ) == null )
		{
			if( ( $request = json_decode( $body ) ) === null || !isset( $request->data ) || !is_array( $request->data ) ) {
				throw new \Aimeos\Admin\JsonAdm\Exception( sprintf( 'Invalid JSON in body' ), 400 );
			}

			$ids = $this->getIds( $request );
			$manager->deleteItems( $ids );
			$view->total = count( $ids );
		}
		else
		{
			$manager->deleteItem( $id );
			$view->total = 1;
		}

		return $view;
	}


	/**
	 * Retrieves the item or items and adds the data to the view
	 *
	 * @param \Aimeos\MW\View\Iface $view View instance
	 * @return \Aimeos\MW\View\Iface View instance with additional data assigned
	 */
	protected function getItem( \Aimeos\MW\View\Iface $view )
	{
		$total = 1;
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), $this->getPath() );
		$include = ( ( $include = $view->param( 'include' ) ) !== null ? explode( ',', $include ) : array() );

		if( ( $id = $view->param( 'id' ) ) == null )
		{
			$search = $this->initCriteria( $manager->createSearch(), $view->param() );
			$view->data = $manager->searchItems( $search, array(), $total );
			$view->childItems = $this->getChildItems( $view->data, $include );
			$view->listItems = $this->getListItems( $view->data, $include );
		}
		else
		{
			$view->data = $manager->getItem( $id, array() );
			$view->childItems = $this->getChildItems( array( $id => $view->data ), $include );
			$view->listItems = $this->getListItems( array( $id => $view->data ), $include );
		}

		$view->refItems = $this->getRefItems( $view->listItems );
		$view->total = $total;

		return $view;
	}

	/**
	 * Returns the view object
	 *
	 * @return \Aimeos\MW\View\Iface View object
	 */
	protected function getView()
	{
		return $this->view;
	}


	/**
	 * Initializes the criteria object based on the given parameter
	 *
	 * @param \Aimeos\MW\Criteria\Iface $criteria Criteria object
	 * @param array $params List of criteria data with condition, sorting and paging
	 * @return \Aimeos\MW\Criteria\Iface Initialized criteria object
	 */
	protected function initCriteria( \Aimeos\MW\Criteria\Iface $criteria, array $params )
	{
		$this->initCriteriaConditions( $criteria, $params );
		$this->initCriteriaSortations( $criteria, $params );
		$this->initCriteriaSlice( $criteria, $params );

		return $criteria;
	}


	/**
	 * Initializes the criteria object with conditions based on the given parameter
	 *
	 * @param \Aimeos\MW\Criteria\Iface $criteria Criteria object
	 * @param array $params List of criteria data with condition, sorting and paging
	 */
	private function initCriteriaConditions( \Aimeos\MW\Criteria\Iface $criteria, array $params )
	{
		if( isset( $params['filter'] ) )
		{
			$existing = $criteria->getConditions();
			$criteria->setConditions( $criteria->toConditions( (array) $params['filter'] ) );

			$expr = array( $criteria->getConditions(), $existing );
			$criteria->setConditions( $criteria->combine( '&&', $expr ) );
		}
	}


	/**
	 * Initializes the criteria object with the slice based on the given parameter.
	 *
	 * @param \Aimeos\MW\Criteria\Iface $criteria Criteria object
	 * @param array $params List of criteria data with condition, sorting and paging
	 */
	private function initCriteriaSlice( \Aimeos\MW\Criteria\Iface $criteria, array $params )
	{
		$start = ( isset( $params['page']['offset'] ) ? (int) $params['page']['offset'] : 0 );
		$size = ( isset( $params['page']['limit'] ) ? (int) $params['page']['limit'] : 25 );

		$criteria->setSlice( $start, $size );
	}


	/**
	 * Initializes the criteria object with sortations based on the given parameter
	 *
	 * @param \Aimeos\MW\Criteria\Iface $criteria Criteria object
	 * @param array $params List of criteria data with condition, sorting and paging
	 */
	private function initCriteriaSortations( \Aimeos\MW\Criteria\Iface $criteria, array $params )
	{
		if( !isset( $params['sort'] ) ) {
			return;
		}

		$sortation = array();

		foreach( explode( ',', $params['sort'] ) as $sort )
		{
			if( $sort[0] === '-' ) {
				$sortation[] = $criteria->sort( '-', substr( $sort, 1 ) );
			} else {
				$sortation[] = $criteria->sort( '+', $sort );
			}
		}

		$criteria->setSortations( $sortation );
	}


	/**
	 * Returns the list of domains that are available as resources
	 *
	 * @param \Aimeos\MW\View\Iface $view View object with "resource" parameter
	 * @return array List of domain names
	 */
	protected function getDomains( \Aimeos\MW\View\Iface $view )
	{
		if( ( $domains = $view->param( 'resource' ) ) == '' )
		{
			/** admin/jsonadm/domains
			 * A list of domain names whose clients are available for the JSON API
			 *
			 * The HTTP OPTIONS method returns a list of resources known by the
			 * JSON API including their URLs. The list of available resources
			 * can be exteded dynamically be implementing a new Jsonadm client
			 * class handling request for this new domain.
			 *
			 * To add the new domain client to the list of resources returned
			 * by the HTTP OPTIONS method, you have to add its name in lower case
			 * to the existing configuration.
			 *
			 * @param array List of domain names
			 * @since 2016.01
			 * @category Developer
			 */
			$default = array(
				'attribute', 'catalog', 'coupon', 'customer', 'locale', 'media',
				'order', 'plugin', 'price', 'product', 'service', 'supplier', 'tag', 'text'
			);
			$domains = $this->getContext()->getConfig()->get( 'admin/jsonadm/domains', $default );
		}

		return (array) $domains;
	}


	/**
	 * Returns the items with parent/child relationships
	 *
	 * @param array $items List of items implementing \Aimeos\MShop\Common\Item\Iface
	 * @param array $include List of resource types that should be fetched
	 * @return array List of items implementing \Aimeos\MShop\Common\Item\Iface
	 */
	protected function getChildItems( array $items, array $include )
	{
		return array();
	}


	/**
	 * Returns the list items for association relationships
	 *
	 * @param array $items List of items implementing \Aimeos\MShop\Common\Item\Iface
	 * @param array $include List of resource types that should be fetched
	 * @return array List of items implementing \Aimeos\MShop\Common\Item\Lists\Iface
	 */
	protected function getListItems( array $items, array $include )
	{
		return array();
	}


	/**
	 * Returns the items associated via a lists table
	 *
	 * @param array $listItems List of items implementing \Aimeos\MShop\Common\Item\Lists\Iface
	 * @return array List of items implementing \Aimeos\MShop\Common\Item\Iface
	 */
	protected function getRefItems( array $listItems )
	{
		$list = $map = array();
		$context = $this->getContext();

		foreach( $listItems as $listItem ) {
			$map[$listItem->getDomain()][] = $listItem->getRefId();
		}

		foreach( $map as $domain => $ids )
		{
			$manager = \Aimeos\MShop\Factory::createManager( $context, $domain );

			$search = $manager->createSearch();
			$search->setConditions( $search->compare( '==', $domain . '.id', $ids ) );

			$list = array_merge( $list, $manager->searchItems( $search ) );
		}

		return $list;
	}


	/**
	 * Returns the IDs sent in the request body
	 *
	 * @param \stdClass $request Decoded request body
	 * @return array List of item IDs
	 */
	protected function getIds( $request )
	{
		$ids = array();

		if( isset( $request->data ) )
		{
			foreach( (array) $request->data as $entry )
			{
				if( isset( $entry->id ) ) {
					$ids[] = $entry->id;
				}
			}
		}

		return $ids;
	}


	/**
	 * Returns the context item object
	 *
	 * @return \Aimeos\MShop\Context\Item\Iface Context object
	 */
	protected function getContext()
	{
		return $this->context;
	}


	/**
	 * Returns the paths to the template files
	 *
	 * @return array List of file system paths
	 */
	protected function getTemplatePaths()
	{
		return $this->templatePaths;
	}


	/**
	 * Returns the path to the client
	 *
	 * @return string Client path, e.g. "product/property"
	 */
	protected function getPath()
	{
		return $this->path;
	}


	/**
	 * Saves new attributes for one or more items
	 *
	 * @param \Aimeos\MW\View\Iface $view View that will contain the "data" and "total" properties afterwards
	 * @param string $body Request body
	 * @param array &$header Associative list of HTTP headers as value/result parameter
	 * @throws \Aimeos\Admin\JsonAdm\Exception If "id" parameter isn't available or the body is invalid
	 * @return \Aimeos\MW\View\Iface Updated view instance
	 */
	protected function patchItems( \Aimeos\MW\View\Iface $view, $body, array &$header )
	{
		if( ( $request = json_decode( $body ) ) === null || !isset( $request->data ) ) {
			throw new \Aimeos\Admin\JsonAdm\Exception( sprintf( 'Invalid JSON in body' ), 400 );
		}

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), $this->getPath() );

		if( is_array( $request->data ) )
		{
			$data = $this->saveData( $manager, $request );

			$view->data = $data;
			$view->total = count( $data );
			$header['Content-Type'] = 'application/vnd.api+json; ext="bulk"; supported-ext="bulk"';
		}
		elseif( ( $id = $view->param( 'id' ) ) != null )
		{
			$request->data->id = $id;
			$data = $this->saveEntry( $manager, $request->data );

			$view->data = $data;
			$view->total = 1;
		}
		else
		{
			throw new \Aimeos\Admin\JsonAdm\Exception( sprintf( 'No ID given' ), 400 );
		}

		return $view;
	}


	/**
	 * Creates one or more new items
	 *
	 * @param \Aimeos\MW\View\Iface $view View that will contain the "data" and "total" properties afterwards
	 * @param string $body Request body
	 * @param array &$header Associative list of HTTP headers as value/result parameter
	 * @return \Aimeos\MW\View\Iface Updated view instance
	 */
	protected function postItems( \Aimeos\MW\View\Iface $view, $body, array &$header )
	{
		if( ( $request = json_decode( $body ) ) === null || !isset( $request->data ) ) {
			throw new \Aimeos\Admin\JsonAdm\Exception( sprintf( 'Invalid JSON in body' ), 400 );
		}

		if( isset( $request->data->id ) || $view->param( 'id' ) != null ) {
			throw new \Aimeos\Admin\JsonAdm\Exception( sprintf( 'Client generated IDs are not supported' ), 403 );
		}


		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), $this->getPath() );

		if( is_array( $request->data ) )
		{
			$data = $this->saveData( $manager, $request );

			$view->data = $data;
			$view->total = count( $data );
			$header['Content-Type'] = 'application/vnd.api+json; ext="bulk"; supported-ext="bulk"';
		}
		else
		{
			$request->data->id = null;
			$data = $this->saveEntry( $manager, $request->data );

			$view->data = $data;
			$view->total = 1;
		}

		return $view;
	}


	/**
	 * Creates of updates several items at once
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager responsible for the items
	 * @param \stdClass $request Object with request body data
	 * @return array List of items
	 */
	protected function saveData( \Aimeos\MShop\Common\Manager\Iface $manager, \stdClass $request )
	{
		$data = array();

		if( isset( $request->data ) )
		{
			foreach( (array) $request->data as $entry ) {
				$data[] = $this->saveEntry( $manager, $entry );
			}
		}

		return $data;
	}


	/**
	 * Saves and returns the new or updated item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager responsible for the items
	 * @param \stdClass $entry Object including "id" and "attributes" elements
	 * @return \Aimeos\MShop\Common\Item\Iface New or updated item
	 */
	protected function saveEntry( \Aimeos\MShop\Common\Manager\Iface $manager, \stdClass $entry )
	{
		if( isset( $entry->id ) ) {
			$item = $manager->getItem( $entry->id );
		} else {
			$item = $manager->createItem();
		}

		$item = $this->addItemData( $manager, $item, $entry, $item->getResourceType() );
		$manager->saveItem( $item );

		if( isset( $entry->relationships ) ) {
			$this->saveRelationships( $manager, $item, $entry->relationships );
		}

		return $manager->getItem( $item->getId() );
	}


	/**
	 * Saves the item references associated via the list
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager responsible for the items
	 * @param \Aimeos\MShop\Common\Item\Iface $item Domain item with an unique ID set
	 * @param \stdClass $relationships Object including the <domain>/data/attributes structure
	 */
	protected function saveRelationships( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, \stdClass $relationships )
	{
		$id = $item->getId();
		$listManager = $manager->getSubManager( 'lists' );

		foreach( (array) $relationships as $domain => $list )
		{
			if( isset( $list->data ) )
			{
				foreach( (array) $list->data as $data )
				{
					$listItem = $this->addItemData( $listManager, $listManager->createItem(), $data, $domain );

					if( isset( $data->id ) ) {
						$listItem->setRefId( $data->id );
					}

					$listItem->setParentId( $id );
					$listItem->setDomain( $domain );

					$listManager->saveItem( $listItem, false );
				}
			}
		}
	}


	/**
	 * Adds the data from the given object to the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item object to add the data to
	 * @param \stdClass $data Object with "attributes" property
	 * @param string $domain Domain of the type item
	 * @return \Aimeos\MShop\Common\Item\Iface Item including the data
	 */
	protected function addItemData(\Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, \stdClass $data, $domain )
	{
		if( isset( $data->attributes ) )
		{
			$attr = (array) $data->attributes;
			$key = str_replace( '/', '.', $item->getResourceType() );

			if( isset( $attr[$key.'.type'] ) )
			{
				$typeItem = $manager->getSubManager( 'type' )->findItem( $attr[$key.'.type'], array(), $domain );
				$attr[$key.'.typeid'] = $typeItem->getId();
			}

			$item->fromArray( $attr );
		}

		return $item;
	}
}
