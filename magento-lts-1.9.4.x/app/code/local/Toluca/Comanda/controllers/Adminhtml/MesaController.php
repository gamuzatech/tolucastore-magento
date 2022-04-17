<?php
/**
 * @package     Toluca_Comanda
 * @copyright   Copyright (c) 2022 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Toluca_Comanda_Adminhtml_MesaController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed ()
	{
	    return Mage::getSingleton ('admin/session')->isAllowed ('toluca/comanda/mesa');
	}

	protected function _initAction ()
	{
		$this->loadLayout ()
            ->_setActiveMenu ('toluca/comanda/mesa')
            ->_addBreadcrumb(
                Mage::helper ('comanda')->__('Mesas Manager'),
                Mage::helper ('comanda')->__('Mesas Manager')
            )
        ;

		return $this;
	}

	public function indexAction ()
	{
	    $this->_title ($this->__('Comanda'));
	    $this->_title ($this->__('Mesas Manager'));

		$this->_initAction ();

		$this->renderLayout ();
	}

	public function newAction ()
	{
	    $this->_title ($this->__('Comanda'));
	    $this->_title ($this->__('Mesas Manager'));
	    $this->_title ($this->__('New Mesa'));

        $id = $this->getRequest ()->getParam ('id');

	    $model = Mage::getModel ('comanda/mesa')->load ($id);

	    $mesaData = Mage::getSingleton ('adminhtml/session')->getMesaData (true);

	    if (!empty ($mesaData))
        {
		    $model->setData ($mesaData);
	    }

	    Mage::register ('mesa_data', $model);

        $this->_initAction ();

	    $this->_addContent ($this->getLayout ()->createBlock ('comanda/adminhtml_mesa_edit'));
        $this->_addLeft ($this->getLayout ()->createBlock ('comanda/adminhtml_mesa_edit_tabs'));

	    $this->renderLayout ();
	}

	public function editAction ()
	{
	    $this->_title ($this->__('Comanda'));
		$this->_title ($this->__('Mesa'));
	    $this->_title ($this->__('Edit Mesa'));

		$id = $this->getRequest()->getParam ('id');

		$model = Mage::getModel ('comanda/mesa')->load ($id);

		if ($model && $model->getId ())
        {
			Mage::register ('mesa_data', $model);

            $this->_initAction ();

			$this->_addContent ($this->getLayout ()->createBlock ('comanda/adminhtml_mesa_edit'));
            $this->_addLeft ($this->getLayout()->createBlock ('comanda/adminhtml_mesa_edit_tabs'));

			$this->renderLayout();
		}
		else
        {
			Mage::getSingleton ('adminhtml/session')->addError (Mage::helper ('comanda')->__('Mesa does not exist.'));

			$this->_redirect ('*/*/index');
		}
	}

	public function saveAction()
	{
		$postData = $this->getRequest ()->getPost ();

		if ($postData)
        {
			try
            {
                $id = $this->getRequest ()->getParam ('id');

				$model = Mage::getModel ('comanda/mesa')
				    ->addData ($postData)
                    ->setData ($id ? 'updated_at' : 'created_at', date ('c'))
				    ->setId ($id)
				    ->save ()
                ;

                if (!empty ($_FILES ['filename']) && is_uploaded_file ($_FILES ['filename']['tmp_name']))
                {
                    $mesaDir = Mage::getBaseDir ('media') . DS . 'comanda' . DS . 'mesa';

                    $image = new Gamuza_File_Uploader ('filename');

                    $image->setAllowedExtensions (array('jpg', 'jpeg', 'png', 'pdf'))
                        ->setAllowCreateFolders (true)
                        ->setAllowRenameFiles (true)
                        ->setFilesDispersion (true)
                        ->save ($mesaDir)
                    ;

                    $model->setFilename ($image->getUploadedFilename ())->save ();
                }

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('comanda')->__('Mesa was successfully saved.'));
				Mage::getSingleton ('adminhtml/session')->setMesaData (false);

				if ($this->getRequest()->getParam ('back'))
                {
					$this->_redirect ('*/*/edit', array ('id' => $model->getId ()));

					return $this;
				}

				$this->_redirect ('*/*/index');

				return $this;
			}
			catch (Exception $e)
            {
				Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());
				Mage::getSingleton ('adminhtml/session')->setMesaData ($this->getRequest ()->getPost ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));

			    return $this;
			}
		}

		$this->_redirect ('*/*/index');
	}

	public function deleteAction ()
	{
        $id = $this->getRequest ()->getParam ('id');

		if ($id > 0)
        {
			try
            {
				$model = Mage::getModel ('comanda/mesa');
			    $model->setId ($id)->delete ();

				Mage::getSingleton ('adminhtml/session')->addSuccess (Mage::helper ('comanda')->__('Mesa was successfully deleted.'));

				$this->_redirect ('*/*/index');

			    return $this;
			}
			catch (Exception $e)
            {
				Mage::getSingleton ('adminhtml/session')->addError ($e->getMessage ());

				$this->_redirect ('*/*/edit', array ('id' => $this->getRequest ()->getParam ('id')));

			    return $this;
			}
		}

		$this->_redirect ('*/*/index');
	}

    public function massStatusAction ()
    {
        $mesaIds = $this->getRequest()->getParam('mesa');
        $status  = $this->getRequest()->getParam('status');

        try
        {
            foreach ($mesaIds as $id)
            {
                $mesa = Mage::getModel ('comanda/mesa')->load ($id);

                if (!strcmp ($mesa->getStatus (), Toluca_Comanda_Helper_Data::MESA_STATUS_FREE))
                {
                    $mesa->setIsActive ($status)
                        ->setUpdatedAt (date ('c'))
                        ->save ()
                    ;
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($mesaIds))
            );
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_getSession ()->addError ($e->getMessage ());
        }
        catch (Exception $e)
        {
            $this->_getSession ()
                ->addException ($e, $this->__('An error occurred while updating the mesa(s) status.')
            );
        }

        $this->_redirect ('*/*/index');
    }
}

