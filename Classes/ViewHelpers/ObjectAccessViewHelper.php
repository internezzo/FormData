<?php
namespace Internezzo\FormData\ViewHelpers;


use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ObjectAccessViewHelper extends AbstractViewHelper
{

    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('object', 'array', 'Object or array ot access a key/property from', true);
        $this->registerArgument('key', 'string', 'key/property', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $object = $arguments['object'];
        $key = $arguments['key'];

        if (is_object($object)) {
            return $object->$key;
        } elseif (is_array($object)) {
            if (array_key_exists($key, $object)) {
                return $object[$key];
            }
        }
        return NULL;
    }
}
