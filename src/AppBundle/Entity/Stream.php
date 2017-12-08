<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * Stream
 *
 * @ORM\Table(name="stream")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StreamRepository")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks()
 */
class Stream
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="stream_images_authors", fileNameProperty="authorLogoName")
     */
    private $authorLogo;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorLogoName;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="stream_images", fileNameProperty="bannerName")
     */
    private $banner;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bannerName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_main", type="boolean")
     */
    private $is_main = false;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Stream
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return Stream
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Stream
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set authorLogo
     *
     * @param File $authorLogo
     *
     * @return Stream
     */
    public function setAuthorLogo($authorLogo)
    {
        $this->authorLogo = $authorLogo;

        return $this;
    }

    /**
     * Get authorLogo
     *
     * @return File
     */
    public function getAuthorLogo()
    {
        return $this->authorLogo;
    }

    /**
     * Set banner
     *
     * @param File $banner
     *
     * @return Stream
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;

        return $this;
    }

    /**
     * Get banner
     *
     * @return File
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @return bool
     */
    public function isMain()
    {
        return $this->is_main;
    }

    /**
     * Set if is main
     *
     * @param bool $is_main
     *
     * @return Stream
     */
    public function setIsMain($is_main)
    {
        $this->is_main = $is_main;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorLogoName()
    {
        return $this->authorLogoName;
    }

    /**
     * @param string $authorLogoName
     * @return Stream
     */
    public function setAuthorLogoName($authorLogoName)
    {
        $this->authorLogoName = $authorLogoName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBannerName()
    {
        return $this->bannerName;
    }

    /**
     * @param string $bannerName
     * @return Stream
     */
    public function setBannerName($bannerName)
    {
        $this->bannerName = $bannerName;

        return $this;
    }
}
