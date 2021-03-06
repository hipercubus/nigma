<?php

/**
 * UserManager encapsulates functionality regarding user info, redirect and validation.
 */
class UserManager
{

	private static $instance = NULL;

	private function __construct() { }

	public static function model()
    {
		if (self::$instance == NULL)
			self::$instance = new self;
		return self::$instance;
    }

    /**
     * Return TRUE if $userID has assign the role specified, FALSE otherwise.
     *
     * If $userID are not specified validate using current user.
     * 
     * @param  mixed   $role 	could be an array or a string
     * @param  int     $userID
     * @return boolean
     */
    public function isUserAssignToRole($role, $userID=NULL)
    {
    	if (Yii::app()->user->id == NULL) // if user is not login return false
    		return false;

    	if ($userID == NULL) // if user is not specified, assigned current user
    		$userID = Yii::app()->user->id;

	    $roles = Yii::app()->authManager->getRoles($userID);
    	if ( is_array($role) ) {
    		foreach ($role as $r)
    			if ( in_array($r, array_keys($roles)) )
    				return true;
    		return false;
    	} else {
			return in_array($role, array_keys($roles));
    	}
    }

    /**
     * If current user is a partner associated user. Redirecto to corresponding index view.
     *
     * If user is not login, this method doesn't redirect.
     */
    public function redirectToIndex()
    {
    	if ($this->isUserAssignToRole('affiliate'))
			Yii::app()->controller->redirect(Yii::app()->baseUrl.'/partners/affiliates');

        if ($this->isUserAssignToRole('advertiser'))
            Yii::app()->controller->redirect(Yii::app()->baseUrl.'/partners/advertisers');
		
        if ($this->isUserAssignToRole('publisher'))
            Yii::app()->controller->redirect(Yii::app()->baseUrl.'/partners/publishers');

        if ($this->isUserAssignToRole('publisherCPM'))
            Yii::app()->controller->redirect(Yii::app()->baseUrl.'/partners/publishersCPM');

        if ($this->isUserAssignToRole('account_manager_admin'))
            Yii::app()->controller->redirect(Yii::app()->baseUrl.'/dailyReport/admin');

        if( $this->isUserAssignToRole('operation_manager') )
            Yii::app()->controller->redirect(Yii::app()->baseUrl.'/dailyReport/admin');       
    }

}