<?php

namespace Modules\Messages\Classes\Traits;

use Exception;
use Illuminate\Support\Facades\View;
use Modules\Messages\Classes\Messager;
use ReflectionClass;


trait MessagerTemplate
{

    protected string $templates_path;

    protected string $template_default_name = 'default.blade.php';

    private const TEMPLATES_DEFAULT_PATH = '/Templates';

    private static string $CLASS_END_NAME = 'Notification';


    private ReflectionClass $reflection_class;

    /**
     * @throws Exception
     */
    public function renderTemplate(Messager $messager, array $arguments_template = []): string
    {

        return View::file($this->getTemplatePath($messager), $arguments_template)->render();
    }


    /**
     * @throws Exception
     */
    private function getTemplatePath(Messager $messager): string
    {
        $this->reflection_class = new ReflectionClass($this::class);

        $template_path = $this->getTemplatesRootPath() . DIRECTORY_SEPARATOR;

        $current_template = $template_path . $messager->getNamePatternFile();
        if (!file_exists($current_template)) {

            $current_template = $template_path . $this->template_default_name;
            if (!file_exists($current_template)) {

                throw new Exception("В директории '$template_path' не существует шаблона '"
                    . $this->template_default_name . "' или '" . $messager->getNamePatternFile() . "'"
                );
            }
        }
        return $current_template;
    }


    private function getTemplatesRootPath(): string
    {

        if (empty($this->templates_path)) {

            $this->templates_path = dirname($this->reflection_class->getFileName())
                . self::TEMPLATES_DEFAULT_PATH
                . DIRECTORY_SEPARATOR
                . $this->getTemplateName();
        }

        return $this->templates_path;
    }


    private function getTemplateName(): string
    {
        return str_replace(self::$CLASS_END_NAME, '', $this->reflection_class->getShortName());
    }


}
