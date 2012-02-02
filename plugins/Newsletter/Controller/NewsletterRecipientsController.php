<?php

/*
* This file is part of BeePublished which is based on CakePHP.
* BeePublished is free software: you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation, either version 3
* of the License, or any later version.
* BeePublished is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public
* License along with BeePublished. If not, see
* http://www.gnu.org/licenses/.
*
* @copyright 2012 Duale Hochschule Baden-W¸rttemberg Mannheim
* @author Marcus Lieberenz
*
* @description Basic Settings for all controllers
*/

class NewsletterRecipientsController extends NewsletterAppController {
	
	var $layout = 'overlay';
	
	public $uses = array('Newsletter.NewsletterRecipient', 'User');
		
	public function index($contentID, $pluginId){
		$recipients = $this->NewsletterRecipient->find('all', array(
			'order' => array(
				'NewsletterRecipient.email' => 'asc'),
			'conditions' => array(
				'NewsletterRecipient.active' => 1))); 
		$this->set('recipients', $recipients);
		$this->set('contentID', $contentID);
		$this->set('pluginId', $pluginId);
	}
	
	public function delete($pluginId, $id){
		$this->PermissionValidation->actionAllowed($pluginId, 'UnSubscribeOtherUsers', true);
		$recipient = $this->NewsletterRecipient->findById($id);
		// delete = set recipient inactive
		$recipient['NewsletterRecipient']['active'] = NULL;
		// save updated recipient
		$this->NewsletterRecipient->set($recipient);
		if($this->NewsletterRecipient->save()){
			$this->Session->setFlash(__d('newsletter','The recipient was deleted successfully.'), 'default', array(
				'class' => 'flash_success'), 
				'RecipientDeleted');
		} else {
			$this->Session->setFlash(__d('newsletter','The recipient couldn\'t be deleted.'), 'default', array(
				'class' => 'flash_failure'), 
				'RecipientDeleted');
		}
		$this->redirect($this->referer());
	}
	
	public function add($pluginId){
		$this->PermissionValidation->actionAllowed($pluginId, 'UnSubscribeOtherUsers', true);
		if ($this->request->is('post')){
			$email = $this->data['NewsletterRecipient']['email'];
			// check, if recipient already exists, but is inactive
			$recipient = $this->NewsletterRecipient->findByEmail($email);
			if (($recipient) && ($recipient['NewsletterRecipient']['active'] == 0)){
				// set active
				$recipient['NewsletterRecipient']['active'] = 1;
			} else {
				// create new recipient
				// check, if recipient is user
				$user = $this->User->findByEmail($email);
				if(isset($user)){
					$recipient = array(
						'NewsletterRecipient' => array(
							'email' => $email,
							'user_id' => $user['User']['id'],
							'active' => '1'));
				} else {
					$recipient = array(
						'NewsletterRecipient' => array(
							'email' => $email,
							'user_id' => NULL,
							'active' => '1'));
				}
			}
			$action = 'add';
			$this->NewsletterRecipient->set($recipient);
			if($this->NewsletterRecipient->save()) {
				if ($action == 'add'){
					$this->Session->setFlash(__d('newsletter','The user was added successfully.'), 'default', array(
						'class' => 'flash_success'), 
						'NewsletterRecipient');
				} else {
					$this->Session->setFlash(__d('newsletter','The user was removed successfully.'), 'default', array(
						'class' => 'flash_success'), 
					'NewsletterRecipient');
				}
			} else {
				$this->Session->setFlash(__d('newsletter','The user was not added.'), 'default', array(
					'class' => 'flash_failure'), 
					'NewsletterRecipient');
				$this->_persistValidation('NewsletterRecipient');
			}
		}
		$this->redirect($this->referer());
	}
	
	public function deleteSelected($contentID, $pluginId){
		$this->PermissionValidation->actionAllowed($pluginId, 'UnSubscribeOtherUsers', true);
		if ($this->request->is('post')){
			$recipients = $this->NewsletterRecipient->find('all', array(
			'order' => array(
				'NewsletterRecipient.email' => 'asc'),
			'conditions' => array(
				'NewsletterRecipient.active' => 1)));
			if (isSet($this->data['selectRecipients'])){
				$selectedRecipients = $this->data['selectRecipients'];
				foreach($recipients as $recipient){
					$id = $recipient['NewsletterRecipient']['id'];
					if ($selectedRecipients[$id] == 1){
						// delete = set recipient inactive
						$recipient['NewsletterRecipient']['active'] = NULL;
						// save updated recipient
						$this->NewsletterRecipient->set($recipient);
						if($this->NewsletterRecipient->save()){
							$this->Session->setFlash(__d('newsletter','The selected recipients have been deleted successfully.'), 'default', array(
								'class' => 'flash_success'), 
								'RecipientDeleted');
						} else {
							$this->Session->setFlash(__d('newsletter','The recipients couldn\'t be deleted.'), 'default', array(
								'class' => 'flash_failure'), 
								'RecipientDeleted');
						}
					}
				}
			} else {
				$this->Session->setFlash(__d('newsletter','You haven\'t selected any recipient to delete.'), 'default', array(
					'class' => 'flash_failure'), 
					'NewsletterDeleted');
			}
			$this->redirect($this->referer());
		}
	}
	
}

