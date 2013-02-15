<?php

namespace Bodaclick\BDKEnquiryBundle\Entity;

use Bodaclick\BDKEnquiryBundle\Model\Enquiry as BaseEnquiry;

/**
 * Enquiry entity, where business objects ("about") and answers are related
 */
class Enquiry extends BaseEnquiry
{
    /**
     * @var string Field used to store a representation of the about object
     */
    protected $aboutDefinition;

    /**
     * Set a definition/representation of the about object based in its classname and ids
     *
     * @param string $className Classname of the about object
     * @param array $ids Ids of the about object
     *
     * @return string
     */
    public function setAboutDefinition($className, array $ids)
    {
        //Field value generated from the parameters
        $this->aboutDefinition = self::generateAboutDefinition($className,$ids);
        return $this->aboutDefinition;
    }

    /**
     * Rebuild the classname and the ids of the about object from the aboutDefinition field
     *
     * @return array
     */
    public function getAboutDefinition()
    {
        $tokens = explode('@',$this->aboutDefinition);

        $className = $tokens[0];
        $plainIds = array_slice($tokens,1);

        $ids = array();

        foreach($plainIds as $id)
        {
            $tokens = explode(':',$id);
            $ids[$tokens[0]]=$tokens[1];
        }

        return compact('className','ids');
    }

    /**
     * Generate the definition/representation of an object given its classname and ids
     *
     * @param string $className Classname of the about object
     * @param array $ids Ids of the about object
     * @return string
     */
    public static function generateAboutDefinition($className, array $ids)
    {
        array_walk($ids,function(&$val,$key){
            $val = $key.':'.$val;
        });

       return implode('@',array_merge((array)$className,$ids));
    }

}

