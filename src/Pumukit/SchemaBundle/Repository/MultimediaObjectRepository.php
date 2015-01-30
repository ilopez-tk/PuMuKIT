<?php

namespace Pumukit\SchemaBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\MultimediaObject;

/**
 * MultimediaObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MultimediaObjectRepository extends DocumentRepository
{
    /**
     * Find all multimedia objects in a series with given status
     *
     * @param Series $series
     * @param array $status
     * @return ArrayCollection
     */
    public function findWithStatus(Series $series, array $status)
    {
        return $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->in($status)
          ->getQuery()
          ->execute()
          ->sort(array('rank', 'desc'));
    }
    
    /**
     * Find multimedia object prototype
     *
     * @param Series $series
     * @param array $status
     * @return MultimediaObject
     */
    public function findPrototype(Series $series)
    {
        return $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->equals(MultimediaObject::STATUS_PROTOTYPE)
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects in a series
     * without the template (prototype)
     *
     * @param Series $series
     * @return ArrayCollection
     */
    public function findWithoutPrototype(Series $series)
    {
        $aux = $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE)
          ->getQuery()
          ->execute()
          ->sort(array('rank', 'desc'));
        
        return $aux;
    }

    /**
     * Find multimedia objects by pic id
     *
     * @param string $picId
     * @return MultimediaObject
     */
    public function findByPicId($picId)
    {
        return $this->createQueryBuilder()
          ->field('pics._id')->equals(new \MongoId($picId))
          ->getQuery()
          ->getSingleResult();
    }
    
    /**
     * Find multimedia objects by person id
     *
     * @param string $personId
     * @return ArrayCollection
     */
    public function findByPersonId($personId)
    {
        return $this->createQueryBuilder()
          ->field('people_in_multimedia_object.people._id')->equals(new \MongoId($personId))
          ->getQuery()
          ->execute();
    }

    /**
     * Find multimedia objects by person id
     * with given role
     *
     * @param string $personId
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findByPersonIdWithRoleCod($personId, $roleCod)
    {
        /* TODO - Fails in this case -> MultimediaObject with: #6100
           Person 1 with Role 1
           Person 2 with Role 2
           
           findByPersonIdWithRoleCode(Person 1, Role 2)
           -> returns this MultimediaObject because it has a person
           with id 1 and has a person with role 2
        */
        return $this->createQueryBuilder()
          ->field('people_in_multimedia_object.people._id')->equals(new \MongoId($personId))
          ->field('people_in_multimedia_object.cod')->equals($roleCod)
          ->getQuery()
          ->execute();
    }

    /**
     * Find series by person id
     *
     * @param string $personId
     * @return ArrayCollection
     */
    public function findSeriesFieldByPersonId($personId)
    {
        return $this->createQueryBuilder()
          ->field('people_in_multimedia_object.people._id')->equals(new \MongoId($personId))
          ->distinct('series')
          ->getQuery()
          ->execute();
    }

    // Find Multimedia Objects with Tags

    /**
     * Find multimedia objects by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createStandardQueryBuilder()
            ->field('tags._id')->equals(new \MongoId($tag->getId()));
        
        if (0 !== count($sort) ){
            $qb->sort($sort);
        }        

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find one multimedia object by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @return MultimediaObject
     */
    public function findOneWithTag($tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->equals(new \MongoId($tag->getId()))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects with any tag
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAnyTag($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->in($mongoIds);
        
        if (0 !== count($sort) ){
            $qb->sort($sort);
        }        
        
        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find multimedia objects with all tags
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->all($mongoIds);
        
        if (0 !== count($sort) ){
            $qb->sort($sort);
        }        

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find one multimedia object with all tags
     *
     * @param array $tags
     * @return MultimediaObject
     */
    public function findOneWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->all($mongoIds);
        
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Find multimedia objects without tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithoutTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->notEqual(new \MongoId($tag->getId()));
        
        if (0 !== count($sort) ){
            $qb->sort($sort);
        }        

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find one multimedia object without tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @return MultimediaObject
     */
    public function findOneWithoutTag($tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->notEqual(new \MongoId($tag->getId()))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects without all tags
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithoutAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->notIn($mongoIds);
        
        if (0 !== count($sort) ){
            $qb->sort($sort);
        }        

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    // End of find Multimedia Objects with Tags

    // Find Series Field with Tags

    /**
     * Find series with tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return ArrayCollection
     */
    public function findSeriesFieldWithTag($tag)
    {
        return $this->createStandardQueryBuilder()
            ->field('tags._id')->equals(new \MongoId($tag->getId()))
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find one series with tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return Series
     */
    public function findOneSeriesFieldWithTag($tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->equals(new \MongoId($tag->getId()))
          ->distinct('series')
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find series with any tag
     *
     * @param array $tags
     * @return ArrayCollection
     */
    public function findSeriesFieldWithAnyTag($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return $this->createStandardQueryBuilder()
            ->field('tags._id')->in($mongoIds)
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find series with all tags
     *
     * @param array $tags
     * @return ArrayCollection
     */
    public function findSeriesFieldWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return  $this->createStandardQueryBuilder()
            ->field('tags._id')->all($mongoIds)
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find one series with all tags
     *
     * @param array $tags
     * @return Series
     */
    public function findOneSeriesFieldWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return  $this->createStandardQueryBuilder()
            ->field('tags._id')->all($mongoIds)
            ->distinct('series')
            ->getQuery()
            ->getSingleResult();
    }

    // End of find Series with Tags

    /**
     * Get mongo ids
     *
     * @param array $documents
     * @return array
     */
    private function getMongoIds($documents)
    {
        $mongoIds = array();
        foreach($documents as $document){
            $mongoIds[] = new \MongoId($document->getId());
        }

        return $mongoIds;
    }

    /**
     * Create standard query builder
     *
     * Creates a query builder with all multimedia objects
     * having status different than PROTOTYPE.
     * These are the multimedia objects we need to show
     * in series.
     * 
     * @return QueryBuilder
     */
    private function createStandardQueryBuilder()
    {
        return $this->createQueryBuilder()
          ->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE);
    }
}
