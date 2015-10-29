##Plugins (concept)##

Plugins are scripts that are loaded when a pluggable module or component is initialized.

As you know most Yii2 application components can be customized via the config.

```php
return [
    // ...
    'components' => [
        'myComponent' => [
          'class' => 'ClassName',
          'propertyName' => 'propertyValue',
          'on eventName' => $eventHandler,
          'as behaviorName' => $behaviorConfig,
        ],
    ],
    'modules' => [
        'myModule' => [
            'class' => 'ClassName',
            'propertyName' => 'propertyValue',
            'on eventName' => $eventHandler,
            'as behaviorName' => $behaviorConfig,
        ],
    ],
];
```

Also many Yii2 extensions may have modules and components that can be customized by attaching events or overriding components and properties.
The idea of a plugin is to let the script do that for you. 

You can think of plugins as pre-init scripts that attach events,
behaviors, controllers, actions and other components before the init() script is run. There are three types of
plugins:

- component plugins
- module plugins
- application plugins

Plugins can be attached to modules and components that implement `comyii\common\interfaces\PluggableInterface`.

You can attach plugins by setting the `plugins` property. Ideally plugins would be loaded automatically either through file includes or
via a database. For simplicity all the plugins in this documentation will be shown as if added to the config.

```php
    'modules' => [
        'myModule' => [
            'class' => 'ClassName',
            'plugins' => [
                'myPlugin' => 'PluginClass',
                'anotherPlugin' => [
                    'class' => 'AnotherPluginClass',
                    'propertyName' => 'value',
                ]
            ]
        ],
    ],
```

The plugin class can be any class that implements the `comyii\common\PluginInterface` interface.

```php
interface PluginInterface
{
    public function __construct(Component $component, $config = []);
    public function init();
}
```

The constructor is passed the parent component and the configuration array. You can customize the components configuration
array before it's created and use the init method to run any code after the parent component has been initialized.

Assume we have the following configuration.

```php
    'components' => [
        'someComponent' => [
            'class' => 'ClassName',
            'plugins' => [
                'myPlugin' => [
                    'class' => 'MyComponentPlugin',
                    'propertyName' => 'b',
                ]
                
            ]
        ],
    ],
```

```php
class MyComponentPlugin implements PluginInterface
{
    public $parent;
    /**
     * The constructor is ran only when the parent component is loaded.
     */
    public function __construct(Component $component, $config = []) {
    
        // the default properties for the parent component can be updated here. However, note that
        // properties set here will be overwritten if set in the config when the parent object is created.
        $component->property = 'a';
        
        $this->parent = $component;
        
    }
    
    /**
     * This is ran after the parent object has been created.
     */
    public function init() {
        $component = $this->parent;
        
        echo 'property: '.$component->property; // property: b
        
        $component->on(Foo::EVENT_HELLO, [$object, 'methodName']);
        $component->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);
        $component->on(Foo::EVENT_HELLO, function ($event) { });

        $component->attachBehavior('myBehavior1', new MyBehavior);
        $component->attachBehavior('myBehavior2', MyBehavior::className());
        $component->attachBehavior('myBehavior3', [
            'class' => MyBehavior::className(),
            'prop1' => 'value1',
            'prop2' => 'value2',
        ]);
    }
}
```

If the component is a module then you can also add controllers and actions to the controller map.
To create a plugin for a module you must implement the `ModulePluginInterface`.

```php
namespace comyii\common\interfaces;

use yii\base\Module;

interface ModulePluginInterface
{
    public function __construct(Module $module, $config = []);
    public function init();
    
}
```

```php
class MyModulePlugin implements ModulePluginInterface
{
    public $parent;
    public function __construct(Module $module, $config = []) {
        $module->controllerMap['extraAction'] = 'example\yii2-my-plugin\controllers\example\ExampleController';
    }
    public function init() {
    }
}
```

Since the plugin classes are loaded everytime the parent component is constructed you should keep these scripts as short
as possible and separate any events into different files.

```php
// use static class methods to take advantage of the autoloader
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);
// instead of anonymous function
$foo->on('beforeAction', function ($event) { 
  // unless beforeAction event
});
```

This will help keep code loaded only when needed. Unless you're attaching code to events you know will run each
time like the `beforeAction` event in the module in which case an anonymous function may be better than loading another file.

This same concept can be applied at the application level for application plugins.

```php
return [
    'plugins' => [
        // the below plugin may add multiple modules and components to the application
        'packageName' => 'PackageClass'
    ],
    'modules' => [
        // ... user defined modules
    ],
];
```

Each pluggable component has it's own DI container used for setting and getting plugins.

##Problems##

- each plugin's init script is loaded each time the pluggable component is constructed. This may cause performance issues 
with larger applications or if a plugin is not organized properly ie. plugin loads all of it's code in the init script 
instead of attaching event handlers to only include code as needed.
- since components are loaded via an init script this may make the application less readable than using a config file.
- if two plugins try to attach modules or components with the same id, this or similar situations could create conflicts
and bugs that are more difficult to track down.

##Benefits##

The benefit of plugins is that the script can attach modules, components, behaviors and events without needing to edit the 
configuration files. This approach can be used to create packages that have one-click installs.

The main purpose of this would be so that applications and extensions can be written in a way that end-users (non-programmers) 
can add additional functionality to their application without having to know how to use composer and edit config files.

##Why Not Both?##

Since the only thing most plugins would be doing is attaching components and updating configurations, which can be done in the config, 
plugins can be made with manual install instructions. So developers could make their extension then add a plugin class as an option
for end-users who want to use a plugin manager instead.


