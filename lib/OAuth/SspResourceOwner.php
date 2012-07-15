<?php

class SspResourceOwner implements IResourceOwner {

    private $_c;
    private $_ssp;

    public function __construct(Config $c) {
        $this->_c = $c;
        $sspPath = $this->_c->getSectionValue('SspResourceOwner', 'sspPath') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '_autoload.php';
        if(!file_exists($sspPath) || !is_file($sspPath) || !is_readable($sspPath)) {
            throw new ResourceOwnerException("invalid path to simpleSAMLphp");
        }
        require_once $sspPath;

        $this->_ssp = new SimpleSAML_Auth_Simple($this->_c->getSectionValue('SspResourceOwner', 'authSource'));
    }

    public function setHint($resourceOwnerIdHint = NULL) {
    }

    public function getResourceOwnerId() {
        $this->_ssp->requireAuth(array("saml:NameIDPolicy" => "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"));
        if($this->_c->getSectionValue('SspResourceOwner', 'useNameID')) {
            $nameId = $this->_ssp->getAuthData("saml:sp:NameID");
            if("urn:oasis:names:tc:SAML:2.0:nameid-format:persistent" !== $nameId['Format']) {
                throw new ResourceOwnerException("NameID format not equal urn:oasis:names:tc:SAML:2.0:nameid-format:persistent");
            }
            return $nameId['Value'];
        } else {
            $attributes = $this->_ssp->getAttributes();
            if(!array_key_exists($this->_c->getSectionValue('SspResourceOwner', 'resourceOwnerIdAttributeName'), $attributes)) {
                throw new ResourceOwnerException("resourceOwnerIdAttributeName is not available in SAML attributes");
            }
            return $attributes[$this->_c->getSectionValue('SspResourceOwner', 'resourceOwnerIdAttributeName')][0];
        }
    }

    public function getResourceOwnerDisplayName() {
        $this->_ssp->requireAuth(array("saml:NameIDPolicy" => "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"));
        $attributes = $this->_ssp->getAttributes();
        if(!array_key_exists($this->_c->getSectionValue('SspResourceOwner', 'resourceOwnerDisplayNameAttributeName'), $attributes)) {
            throw new ResourceOwnerException("resourceOwnerDisplayNameAttributeName is not available in SAML attributes");
        }
        return $attributes[$this->_c->getSectionValue('SspResourceOwner', 'resourceOwnerDisplayNameAttributeName')][0];
    }

}

?>