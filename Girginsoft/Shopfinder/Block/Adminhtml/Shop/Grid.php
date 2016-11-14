<?php
namespace Girginsoft\Shopfinder\Block\Adminhtml\Shop;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_countryFactory;

    /**
     * @var \Girginsoft\Shopfinder\Model\ResourceModel\Shop\Collection
     */
	protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Girginsoft\Shopfinder\Model\ResourceModel\Shop\Collection $collectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @internal param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @internal param \Magento\Catalog\Model\ProductFactory $productFactory
     * @internal param \Magento\Catalog\Model\Product\Type $type
     * @internal param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @internal param \Magento\Catalog\Model\Product\Visibility $visibility
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
		\Girginsoft\Shopfinder\Model\ResourceModel\Shop\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Directory\Model\Config\Source\Country $_countryFactory,
        array $data = []
    ) {
		$this->_countryFactory = $_countryFactory;
		$this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('productGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
       
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		try{
			
			
			$collection =$this->_collectionFactory->load();

		  

			$this->setCollection($collection);

			parent::_prepareCollection();
		  
			return $this;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();die;
		}
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'shop_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
            'shop_name',
            [
                'header' => __('Shop Name'),
                'index' => 'shop_name',
                'class' => 'shop_name'
            ]
        );

        $this->addColumn(
            'identifier',
            [
                'header' => __('Identifier'),
                'index' => 'identifier',
                'class' => 'identifier'
            ]
        );

        $countries = $this->_countryFactory->toOptionArray();
        $options = [];
        foreach ($countries as $country) {
            if (empty($country["value"])) {
                continue;
            }
            $options[$country["value"]] = $country["label"];
        }
        //var_dump($options);

        $this->addColumn(
            'country',
            [
                'header' => __('Country'),
                'index' => 'country',
                'type' => 'select',
                'class' => 'country',
                'options' => $options

            ]
        );


        $this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'index' => 'image',
                'type' => 'image',
                'class' => 'image'
            ]
        );

        $this->addColumn(
            'creation_time',
            [
                'header' => __('Created At'),
                'type' => 'datetime',
                'index' => 'creation_time',
                'class' => 'creation_time'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

     /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('shopfinder/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('shopfinder/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'shopfinder/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
}
