## Как использовать

В любом месте нужно создать класс заканчивающийся префиксом `Notification`<br>
В той же директории создать каталог "`Templates`"

Пример каталога:

``` 
Notifications
--NewVisitNotification.php
--ReminderVisitNotification.php
--Templates
----NewVisit
------default.blade.php
------WhatsApp.blade.php
----ReminderVisit
------default.blade.php
```

### **Базовая настройка уведомления** на примере NewVisitNotification.php:
``` php
class NewVisitNotification extends Notification
{
    public function reactionNewMessage(Message $incoming_message): bool
    {
        Log::alert('Я реакция на входящее сообщение. Во мне можно, например, отправить ответное сообщение', $incoming_message);
        return true; //подавление остальных событий
    }
    
    protected function templateArgumentsTransform(array &$arguments): void
    {
        //Редактируем переменные перед отправкой в шаблон
        $arguments['name'] = ucfirst($arguments['name']);  
    }
}
``` 

## Отправка уведомления
В любом месте кода, где нужно отправить уведомление, вызываем метод созданного ранее класса:

``` php
NewVisitNotification::setPhone(795212345567)->send();
``` 

Отправка в определенный месседжер:
``` php
NewVisitNotification::setPhone(795212345567)
            ->setMessager(new VK()) //при выборе месседжера отключается каскад
            ->setTemplateArguments(name: 'Олег')
            ->send();
``` 

Отправка с измененными настройками каскада:
``` php
NewVisitNotification::setPhone(79523949280)
            ->useCascade([
                WhatsApp::class => 0,
                VK::class => 200,
                SMS::class => 0
            ])
            ->setTemplateArguments(name: 'Олег')
            ->send();
``` 
Отправится сразу через WhatsApp и VK, если через 200 секунд не прочитают, то отправится через SMS

Также очередность отправления можно изменить в самом классе переопределив $messagers_sequence
