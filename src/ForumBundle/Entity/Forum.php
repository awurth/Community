<?php

namespace ForumBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="forum_forum")
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "get_forum_forum",
 *         parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "category",
 *     href = @Hateoas\Route(
 *         "get_forum_category",
 *         parameters = { "id" = "expr(object.getCategory().getId())" }
 *     )
 * )
 */
class Forum
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     * @ORM\Column(name="title", type="string", length=100)
     */
    protected $title;

    /**
     * @var string
     *
     * @Assert\Length(max=100)
     * @ORM\Column(name="description", type="string", length=100, nullable=true)
     */
    protected $description;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="change", field={"title", "description"})
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var Category
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ForumBundle\Entity\Category", cascade={"persist"}, inversedBy="forums")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Exclude
     */
    protected $category;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ForumBundle\Entity\Topic", mappedBy="forum")
     *
     * @JMS\Exclude
     */
    protected $topics;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    /**
     * Gets the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return Forum
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return Forum
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the creation date.
     *
     * @param DateTime $createdAt
     *
     * @return Forum
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the creation date.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the last update date.
     *
     * @param DateTime $updatedAt
     *
     * @return Forum
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the last update.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Adds a topic.
     *
     * @param Topic $topic
     *
     * @return self
     */
    public function addTopic(Topic $topic)
    {
        $this->topics[] = $topic;
        $topic->setForum($this);

        return $this;
    }

    /**
     * Removes a topic.
     *
     * @param Topic $topic
     */
    public function removeTopic(Topic $topic)
    {
        $this->topics->removeElement($topic);
    }

    /**
     * Gets all topics.
     *
     * @return ArrayCollection
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Sets the category.
     *
     * @param Category $category
     *
     * @return self
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Gets the category.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
