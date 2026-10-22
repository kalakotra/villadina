<?php

declare(strict_types=1);

namespace App\Elements\Traits;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Lets editors emphasise words in plain-text titles: "Villa *Dina*" → Villa <em>Dina</em>.
 * The text is escaped first, so only the *…* markup becomes HTML.
 */
trait FormatsHeading
{
    public function TitleHTML(): DBHTMLText
    {
        return self::formatHeading((string) $this->Title);
    }

    /**
     * Title without the *…* markers (for alt texts, meta, aria).
     */
    public function TitlePlain(): string
    {
        return str_replace('*', '', (string) $this->Title);
    }

    public static function formatHeading(string $text): DBHTMLText
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = (string) preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $escaped);
        $html = nl2br($html, false);

        return DBField::create_field('HTMLFragment', $html);
    }
}
