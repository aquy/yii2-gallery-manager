<?php

namespace aquy\gallery;

use Yii;
use yii\helpers\Url;
use yii\base\Widget;
use yii\helpers\Json;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * Widget to manage gallery.
 * Requires Twitter Bootstrap styles to work.
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryManager extends Widget
{
    /** @var ActiveRecord */
    public $model;

    /** @var string */
    public $behaviorName;

    /** @var GalleryBehavior Model of gallery to manage */
    protected $behavior;

    /** @var string Route to gallery controller */
    public $apiRoute = false;

    public $options = array();


    public function init()
    {
        parent::init();
        $this->behavior = $this->model->getBehavior($this->behaviorName);
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['galleryManager/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@aquy/gallery/messages',
            'fileMap' => [],
        ];
    }


    /** Render widget */
    public function run()
    {
        if ($this->apiRoute === null) {
            throw new Exception('$apiRoute must be set.', 500);
        }

        $images = array();
        foreach ($this->behavior->getImages() as $image) {
            $images[] = array(
                'id' => $image->id,
                'sort' => $image->sort,
                'src' => $image->src,
                'name' => (string)$image->name,
                'description' => (string)$image->description,
                'preview' => $image->getUrl($image->src),
            );
        }

        $baseUrl = [
            $this->apiRoute,
            'type' => $this->behavior->type,
            'behaviorName' => $this->behaviorName,
            'galleryId' => $this->behavior->getGalleryId()
        ];

        $opts = array(
            'hasName' => $this->behavior->hasName ? true : false,
            'hasDesc' => $this->behavior->hasDescription ? true : false,
            'uploadUrl' => Url::to($baseUrl + ['action' => 'ajaxUpload']),
            'deleteUrl' => Url::to($baseUrl + ['action' => 'delete']),
            'updateUrl' => Url::to($baseUrl + ['action' => 'changeData']),
            'rotateUrl' => Url::to($baseUrl + ['action' => 'rotate']),
            'arrangeUrl' => Url::to($baseUrl + ['action' => 'order']),
            'nameLabel' => Yii::t('galleryManager/main', 'Name'),
            'descriptionLabel' => Yii::t('galleryManager/main', 'Description'),
            'messages' => array(
                'edit' => Yii::t('galleryManager/main', 'Edit'),
                'remove' => Yii::t('galleryManager/main', 'Remove'),
                'rotate' => Yii::t('galleryManager/main', 'Rotate'),
            ),
            'photos' => $images,
        );

        $opts = Json::encode($opts);
        $view = $this->getView();
        GalleryManagerAsset::register($view);
        $view->registerJs("$('#{$this->id}').galleryManager({$opts});");

        $this->options['id'] = $this->id;
        $this->options['class'] = 'gallery-manager';

        return $this->render('galleryManager');
    }

}
