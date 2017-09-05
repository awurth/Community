<?php

namespace ForumBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\User;
use Hateoas\Configuration\Annotation as Hateoas;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="forum_post")
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "get_forum_post",
 *         parameters = { "id" = "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "author",
 *     href = @Hateoas\Route(
 *         "get_user",
 *         parameters = { "id" = "expr(object.getAuthor().getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "topic",
 *     href = @Hateoas\Route(
 *         "get_forum_topic",
 *         parameters = { "id" = "expr(object.getTopic().getId())" }
 *     )
 * )
 */
class Post
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
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

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
     * @Gedmo\Timestampable(on="change", field={"content"})
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var User
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Exclude
     */
    protected $author;

    /**
     * @var Topic
     *
     * @ORM\ManyToOne(targetEntity="ForumBundle\Entity\Topic", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Exclude
     */
    protected $topic;

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
     * Sets the content.
     *
     * @param string $content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Gets the content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the creation date.
     *
     * @param DateTime $createdAt
     *
     * @return self
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
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the last update date.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the topic.
     *
     * @param Topic $topic
     *
     * @return self
     */
    public function setTopic(Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Gets the topic.
     *
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Sets the author.
     *
     * @param User $author
     *
     * @return self
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Gets the author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
