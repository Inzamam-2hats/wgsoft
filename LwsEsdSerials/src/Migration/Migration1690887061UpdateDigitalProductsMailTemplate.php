<?php declare(strict_types=1);

namespace LwsEsdSerials\Migration;

use Doctrine\DBAL\Connection;
use LwsProductDesigner\BecUtils\MediaUtil;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1690887061UpdateDigitalProductsMailTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1690887061;
    }

    public function update(Connection $connection): void
    {

        $sql = 'SELECT mtt.mail_template_id, mtt.language_id, mtt.content_html, mtt.content_plain 
                        FROM mail_template_translation mtt
                        JOIN mail_template mt  ON mtt.mail_template_id = mt.id 
                        JOIN mail_template_type mttype ON  mttype.id = mt.mail_template_type_id 
                        WHERE mttype.technical_name = ?';

        $mailTemplates = $connection->fetchAllAssociative($sql, ['downloads_delivery']);
        $addedMailTemplatePart = $this->getAddedTemplatePart();
        $addedMailTemplatePartPlain = $this->getAddedTemplatePartPlain();

        foreach($mailTemplates as $mailTemplate){

            $replaceString = '{{ lineItem.label|u.wordwrap(80) }}';
            $replaceStringPlain = '({{ lineItem.payload.productNumber|u.wordwrap(80) }}){% endif %}';

            if (!str_contains($mailTemplate['content_html'], 'block lws_esd_serials')) {
                $html = str_replace(
                    $replaceString,
                    $replaceString . "\n\n" . $addedMailTemplatePart,
                    $mailTemplate['content_html']
                );

                $plain = str_replace(
                    $replaceStringPlain,
                    $replaceStringPlain . "\n\n" . $addedMailTemplatePartPlain,
                    $mailTemplate['content_plain']
                );

                $connection->update('mail_template_translation', ['content_html' => $html, 'content_plain' => $plain],
                    [
                        'language_id' => $mailTemplate['language_id'],
                        'mail_template_id' => $mailTemplate['mail_template_id'],
                    ]);
            }
        }
    }


    public function updateDestructive(Connection $connection): void
    {
    }

    private function getAddedTemplatePartPlain(): string {
        return "{% block lws_esd_serials %} {% if lineItem.extensions && lineItem.extensions.lwsEsdSerials is defined %}
{{ 'lws-esd-serials.serialNumber'|trans }}:
{% for serial in lineItem.extensions.lwsEsdSerials %} {{ serial.serialNumber }}\n {% endfor %}
{% endif %} {% endblock %}";
    }

    private function getAddedTemplatePart(): string {
        return '
            {% block lws_esd_serials %}
            {% if lineItem.extensions && lineItem.extensions.lwsEsdSerials is defined %}
                <br/>
                <div class="serial-numbers">
                    <div class="serial-numbers-label"><b>{{ "lws-esd-serials.serialNumber"|trans }}:</b></div>
                    {% for serial in lineItem.extensions.lwsEsdSerials %}
                        <div class="serial">{{ serial.serialNumber }}</div>
                    {% endfor %}
                </div>
            {% endif %}
        {% endblock %}
        ';
    }
}
