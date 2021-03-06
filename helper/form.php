<?php
namespace Mu\Kernel\Helper;

use Mu\Kernel;
use Mu\Kernel\Model\Entity;
use Mu\Kernel\Model\Manager;

/**
 * Helper Form
 *
 * Service that allow to generate a form from a Model\Manager
 *
 * @package Mu\Kernel\Session
 * @author Olivier Stahl
 */
class Form extends Kernel\Service\Core
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    const TYPE_DEFAULT = 'text';
    const TYPE_INPUT = 'input';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_PASSWORD = 'password';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_CHECK = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_SELECT = 'select';
    const TYPE_CBLIST = 'checkboxlist';

    protected $action = '';
    protected $enctype = 'multipart/form-data';
    protected $method = self::METHOD_POST;
    protected $allowedMethod = array(self::METHOD_GET, self::METHOD_POST);
    protected $allowedTypes = array(
        self::TYPE_DEFAULT,
        self::TYPE_HIDDEN,
        self::TYPE_PASSWORD,
        self::TYPE_TEXTAREA,
        self::TYPE_CHECK,
        self::TYPE_RADIO,
        self::TYPE_SELECT,
        self::TYPE_CBLIST
    );
    protected $defaultField = array(
        'type' => self::TYPE_DEFAULT,
        'value' => null,
        'default' => '', // Default value
        'class' => '', // Specific field class
        'required' => false, // Is field required
        'pattern' => '', // Pattern to add to input fields (HTML5)
        'option' => '', // Value of the option checked in checkbox
        'allowedValues' => [], // Values for radio and select
        'multiple' => false, // Multiple select declarator
        'separator' => ',', // Default separator (for DB storage)
        'permissions' => array('read'=>true, 'write'=>true)
    );

    /**
     * @param mixed $submit
     * @param string $class
     * @return array
     */
    public function getFormInfos($submit = null, $class = '')
    {
        return array(
            'action' => $this->action,
            'method' => $this->method,
            'enctype' => $this->enctype,
            'class' => $class,
            'submit' => $submit,
            'fieldAfter' => '<br />',
            'fieldBefore' => '',
        );
    }

    /**
     * Generate the formated array containing data for the form rendering
     * @param \Mu\Kernel\Model\Entity|\Mu\Kernel\Model\Manager $object
     * @param array $properties
     * @param string $group
     * @param string $lang
     * @return array
     */
    public function getFields($object, array $properties = array(), $group = null, $lang = null)
    {
        // Get entity and manager:
        if (is_a($object, '\\Mu\\Kernel\\Model\\Entity')) {
            $manager = $object->getManager();
            /** @var Entity $entity */
            $entity = $object;
        } else {
            /** @var Manager $manager */
            $manager = $object;
            $entity = null;
        }

        // Get manager key:
        $allProperties = $manager->getProperties();
        if (!isset($allProperties[$group])) {
            $group = $manager->getDefaultGroup();
        }

        // Get manager properties or return array:
        if (!is_array($allProperties[$group]['properties'])) {
            return array();
        }

        // Get viewer to test permissions:
        $defGroup = $manager->getDefaultGroup(); // TODO remove this when Permity::hadPermissionToProperty can use groups
        /** @var \Mu\Bundle\Users\Model\Entity\User $viewer */
        $viewer   = $this->getViewer();
        $bPerm    = ($viewer && method_exists($viewer, 'hasPermissionToProperty'));

        // Get properties to check:
        $properties = (!empty($properties))
            ? array_intersect_key($allProperties[$group]['properties'], array_flip($properties))
            : $allProperties[$group]['properties'];

        // Start form creation:
        $form = [];
        foreach ($properties as $id => $values) {
            if (!is_array($values['form'])) {
                continue;
            }

            // Set generalities:
            $field = array_merge($this->defaultField, $values['form']);
            $field['label']  = (isset($values['title'])) ? $values['title'] : null;
            $field['length'] = (isset($values['database']['length'])) ? $values['database']['length'] : null;

            // Set permissions:
            if ($bPerm && $group == $defGroup) { // TODO remove this when Permity::hadPermissionToProperty can use groups
                $field['permissions'] = array(
                    'read' => $viewer->hasPermissionToProperty($group, $id, 1),
                    'write'=> $viewer->hasPermissionToProperty($group, $id, 2)
                );
            }

            // Check input type:
            if (!isset($field['type']) || $field['type'] == self::TYPE_INPUT) {
                $field['type'] = self::TYPE_DEFAULT;
            }

            // Set default values for radio and select:
            if (in_array($field['type'], [self::TYPE_RADIO, self::TYPE_SELECT, self::TYPE_CBLIST]) && !is_array($field['allowedValues'])) {
                if (method_exists($object, $field['allowedValues'])) {
                    $field['allowedValues'] = $object->$field['allowedValues']();
                } else if (method_exists($manager, $field['allowedValues'])) {
                    $field['allowedValues'] = $manager->$field['allowedValues']();
                } else if (isset($field['allowedValues'])) {
                    $field['allowedValues'] = (is_string($field['allowedValues'])) ? explode(',', $field['allowedValues']) : $field['allowedValues'];
                } else {
                    continue;
                }

                if (isset($field['none'])) {
                    $field['allowedValues'][0] = $field['none'];
                    ksort($field['allowedValues']);
                }
            }

            // Add value if entity is found and its method too:
            $value = null;
            if (is_a($object, '\Mu\Kernel\Model\Entity')) {
                if ($lang == null) {
                    $getter = (isset($field['getter'])) ? $field['getter'] : 'get' . ucfirst($id);
                    if ($entity != null and method_exists($entity, $getter)) {
                        $value = $entity->$getter();
                    }
                } else {
                    $value = $object->getLocalizedValue($id, $lang);
                }
            }

            if (!empty($value)) {
                $field['value'] = $value;
            }

            // Add lang:
            if ($lang != null) {
                $id .= '_' . $lang;
            }

            // Add default input name:
            if (!isset($field['name'])) {
                $field['name'] = $id;
            }

            // Add default field ID:
            if (!isset($field['id'])) {
                $field['id'] = $id;
            }

            // Add field into the form array:
            $form[$id] = $field;
        }



        // Return formated array:
        return $form;
    }

    /**
     * @param \Mu\Kernel\Model\Entity|\Mu\Kernel\Model\Manager $object
     * @param $field
     * @param string $group
     * @param string $lang
     * @return array|null
     */
    public function getField($object, $field, $group = null, $lang = null)
    {
        $fields = $this->getFields($object, array($field), $group, $lang);
        $return = reset($fields);
        $return['object'] = $object;
        return $return;
    }

    /**
     * Test if all requeried fields are set
     * @param \Mu\Kernel\Model\Entity|\Mu\Kernel\Model\Manager $object
     * @param array $properties
     * @return bool
     */
    public function isValid($object, array $properties = array())
    {
        // Get manager fields:
        $fields = $this->getField($object, $properties);

        // Get request:
        $request = $this->getApp()->getHttp()->getRequest();

        // Test fields:
        foreach ($fields as $id => $field) {
            if (!$field['required']) {
                continue;
            }

            // Get value from POST:
            $value = $request->post($id, null);
            if ($value === null) {
                return false;
            }
        }

        // Return true:
        return true;
    }

    /**
     * Set action of the form
     * @param $route
     * @param array $parameters
     */
    public function setAction($route, array $parameters = array())
    {
        $this->action = $this->getApp()->getRouteManager()->getUrl($route, $parameters);
    }

    /**
     * Set submit method
     * @param string $method
     */
    public function setMethod($method = self::METHOD_POST)
    {
        if (in_array($method, $this->allowedMethod)) {
            $this->method = $method;
        }
    }

    /**
     * Set encrypt type
     * @param $type
     */
    public function setEnctype($type)
    {
        $this->enctype = $type;
    }

    /**
     * @return bool|\Mu\Bundle\Users\Interfaces\Authenticable
     */
    private function getViewer()
    {
        $app = $this->getApp();
        if (method_exists($app, 'getAuthenticationService')) {
            return $app->getAuthenticationService()->getViewer();
        } else {
            return false;
        }
    }
}
