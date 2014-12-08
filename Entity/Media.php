<?php

namespace Htime\LightCmsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 *
 * @ORM\Table(name="htime_cms_media")
 * @ORM\Entity(repositoryClass="Htime\LightCmsBundle\Repository\MediaRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields="name", message="Another media has already this name.")
 */
class Media
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdate", type="datetime")
     */
    private $lastUpdate;

    /**
     * @Assert\File(maxSize="6000000", maxSizeMessage="Maximum file size is 6 Mo.")
     */
    private $file;

    /**
     * On ajoute cet attribut pour y stocker le nom du fichier temporairement.
     */
    private $tempFileName;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lastUpdate = new \Datetime();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return Media
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir(). '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * Retourne le chemin relatif vers le fichier pour le code PHP.
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/' . $this->getUploadDir();
    }

    /**
     * Retourne le chemin relatif vers le fichier pour un navigateur
     */
    protected function getUploadDir()
    {
        return 'uploads/media';
    }

    public static function getWebDir()
    {
        return 'uploads/media';
    }

    /**
     * Set file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (isset($this->path)) {

            // On sauvegarde le nom du fichier pour le supprimer plus tard
            $this->tempFileName = $this->path;

            // On réinitialise la valeur du chemin
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Update lastUpdate
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateLastUpdate()
    {
        $this->setLastUpdate(new \DateTime());
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {

            // On crée un encodage pour le chemin du fichier dans le but de le rendre unique
            $fileName = sha1(uniqid(mt_rand(), true));

            // On génère le chemin en ajoutant l'extension du fichier
            $this->path = $fileName . '.' . $this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif)
        if (null === $this->getFile()) {
            return;
        }

        // On déplace le fichier envoyé dans le bon répertoire
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // Si on avait un ancien fichier, on le supprime
        if (isset($this->tempFileName)) {

            $oldFile = $this->getUploadRootDir(). '/' . $this->tempFileName;

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            // On réinitialise la variable tempFileName à null
            $this->tempFileName = null;
        }

        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier
        $this->tempFileName = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // En PostRemove, on n'a pas accès à l'id, on utilise alors le nom de fichier temporaire
        if (file_exists($this->tempFileName)) {
            unlink($this->tempFileName);
        }
    }
}
