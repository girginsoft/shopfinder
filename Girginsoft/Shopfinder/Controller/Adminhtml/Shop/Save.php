<?php
namespace Girginsoft\Shopfinder\Controller\Adminhtml\Shop;

use Girginsoft\Shopfinder\Model\Shop;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Girginsoft\Shopfinder\Controller\Adminhtml\Shop
 */
class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {

        $data = $this->getRequest()->getParams();
        if ($data) {
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $model = $this->_objectManager->create('Girginsoft\Shopfinder\Model\Shop');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            if(!isset($data['image']['delete']) && isset($data['image']['value'])) {
                $data['image'] = $data['image']['value'];
            }
            $model->setData($data);
            if(isset($data['image']['delete'])) {
                $model->setImage();
                $path = $mediaDirectory->getAbsolutePath($data['image']['value']);
                if (file_exists($path)) {
                    unlink($path);
                }

            }
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        array('fileId' => 'image')
                    );
                    $result = $uploader->save($mediaDirectory->getAbsolutePath(Shop::SHOP_IMAGE_FOLDER));
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'png'));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    unset($result['tmp_name']);
                    unset($result['path']);
                    $data['image'] = $result['file'];
                    $model->setImage($data['image']);
                } catch (Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving image.'));
                }
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Shop has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Zend_Db_Statement_Exception $e) {
                if ($e->getCode() == 23000) {
                    $this->messageManager->addException($e, __('Identifier is already in use'));
                } else {
                    $this->messageManager->addException($e, __('Something went wrong in database while saving the shop.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the shop.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('shop_id')));

            return;
        }
        $this->_redirect('*/*/');
    }
}
