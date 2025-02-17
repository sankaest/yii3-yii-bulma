<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Bulma;

use InvalidArgumentException;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\CustomTag;
use Yiisoft\Html\Tag\I;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Widget\Widget;

use function array_key_exists;
use function implode;
use function is_array;
use function strtr;

/**
 * The Bulma breadcrumb is a simple navigation component.
 *
 * ```php
 * echo Breadcrumbs::widget()->items([
 *     ['label' => 'Info'],
 *     ['label' => 'Contacts'],
 * ]);
 * ```
 *
 * @link https://bulma.io/documentation/components/breadcrumb/
 */
final class Breadcrumbs extends Widget
{
    private string $activeItemTemplate = "<li class=\"is-active\"><a aria-current=\"page\">{link}</a></li>\n";
    private array $attributes = [];
    private string $autoIdPrefix = 'w';
    private bool $encode = false;
    private ?array $homeItem = ['label' => 'Home', 'url' => '/'];
    private array $items = [];
    private array $itemsAttributes = [];
    private string $itemTemplate = "<li>{link}</li>\n";

    /**
     * Returns a new instance with the specified active item template.
     *
     * @param string $value The template used to render each active item in the breadcrumbs. The token `{link}` will be
     * replaced with the actual HTML link for each active item.
     *
     * @return self
     */
    public function activeItemTemplate(string $value): self
    {
        $new = clone $this;
        $new->activeItemTemplate = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified `aria-label` attribute for the current element.
     *
     * @param string $value The value of the aria-label attribute.
     *
     * @return self
     *
     * @link https://www.w3.org/TR/wai-aria/#aria-label
     */
    public function ariaLabel(string $value): self
    {
        $new = clone $this;
        $new->attributes['aria-label'] = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified HTML attributes for widget.
     *
     * @param array $values Attribute values indexed by attribute names.
     *
     * @return self
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} For details on how attributes are being rendered.
     */
    public function attributes(array $values): self
    {
        $new = clone $this;
        $new->attributes = $values;
        return $new;
    }

    /**
     * Returns a new instance with the specified prefix to the automatically generated widget IDs.
     *
     * @param string $value The prefix to the automatically generated widget IDs.
     *
     * @return self
     */
    public function autoIdPrefix(string $value): self
    {
        $new = clone $this;
        $new->autoIdPrefix = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified whether the tags for the breadcrumbs are encoded.
     *
     * @param bool $value Whether to encode the output.
     *
     * @return self
     */
    public function encode(bool $value): self
    {
        $new = clone $this;
        $new->encode = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified first item in the breadcrumbs (called home link).
     *
     * If a `null` is specified, the home item will not be rendered.
     *
     * @param array|null $value Please refer to {@see items()} on the format.
     *
     * @throws InvalidArgumentException If an empty array is specified.
     *
     * @return self
     */
    public function homeItem(?array $value): self
    {
        if ($value === []) {
            throw new InvalidArgumentException(
                'The home item cannot be an empty array. To disable rendering of the home item, specify null.',
            );
        }

        $new = clone $this;
        $new->homeItem = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified ID of the widget.
     *
     * @param string $value The ID of the widget.
     *
     * @return self
     */
    public function id(string $value): self
    {
        $new = clone $this;
        $new->attributes['id'] = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified list of items.
     *
     * @param array $value List of items to appear in the breadcrumbs. If this property is empty, the widget will not
     * render anything. Each array element represents a single item in the breadcrumbs with the following structure:
     *
     * ```php
     * [
     *     'label' => 'label of the link', // required
     *     'url' => 'url of the link', // optional, will be processed by Url::to()
     *     'template' => 'own template of the item', // optional, if not set $this->itemTemplate will be used
     *     'encode' => true/false, // optional, is encoded is `true`, the tags will be encoded
     *     'icon' => 'icon css class', // optional, icon css class
     *     'iconAttributes' => [], // the html attributes for icon container
     * ]
     * ```
     *
     * @return self
     */
    public function items(array $value): self
    {
        $new = clone $this;
        $new->items = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified items HTML attributes.
     *
     * @param array $value The HTML attributes for the item's widget.
     *
     * @return self
     *
     * {@see Html::renderTagAttributes()} For details on how attributes are being rendered.
     */
    public function itemsAttributes(array $value): self
    {
        $new = clone $this;
        $new->itemsAttributes = $value;
        return $new;
    }

    /**
     * Returns a new instance with the specified item template.
     *
     * @param string $value The template used to render each inactive item in the breadcrumbs. The token `{link}` will
     * be replaced with the actual HTML link for each inactive item.
     *
     * @return self
     */
    public function itemTemplate(string $value): self
    {
        $new = clone $this;
        $new->itemTemplate = $value;
        return $new;
    }

    protected function run(): string
    {
        if (empty($this->items)) {
            return '';
        }

        $attributes = $this->attributes;
        $customTag = CustomTag::name('nav');

        Html::addCssClass($attributes, 'breadcrumb');

        if (!array_key_exists('aria-label', $attributes)) {
            $customTag = $customTag->attribute('aria-label', 'breadcrumbs');
        }

        if (!array_key_exists('id', $attributes)) {
            $customTag = $customTag->id(Html::generateId($this->autoIdPrefix) . '-breadcrumbs');
        }

        $content = PHP_EOL . Html::openTag('ul', $this->itemsAttributes) . PHP_EOL .
            implode('', $this->renderItems()) .
            Html::closeTag('ul') . PHP_EOL;

        return $customTag
            ->content($content)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    private function renderIcon(?string $icon, array $iconAttributes): string
    {
        $html = '';

        if ($icon !== null) {
            $html = Span::tag()
                ->attributes($iconAttributes)
                ->content(I::tag()
                    ->attributes(['class' => $icon, 'aria-hidden' => 'true'])
                    ->render())
                ->encode($this->encode)
                ->render();
        }

        return $html;
    }

    /**
     * Renders a single breadcrumb item.
     *
     * @param array $item The item to be rendered. It must contain the "label" element. The "url" element is optional.
     * @param string $template The template to be used to render the link. The token "{link}" will be replaced by the
     * link.
     *
     * @throws InvalidArgumentException If `$item` does not have "label" element.
     *
     * @return string The rendering result.
     */
    private function renderItem(array $item, string $template): string
    {
        if (!array_key_exists('label', $item)) {
            throw new InvalidArgumentException('The "label" element is required for each link.');
        }

        /** @var bool */
        $encode = $item['encode'] ?? $this->encode;
        unset($item['encode']);

        /** @var string|null */
        $icon = $item['icon'] ?? null;
        unset($item['icon']);

        /** @var array */
        $iconAttributes = $item['iconAttributes'] ?? [];
        unset($item['iconAttributes']);

        /** @var string */
        $template = $item['template'] ?? $template;
        unset($item['template']);

        /** @var string|null */
        $url = $item['url'] ?? null;
        unset($item['url']);

        /** @var string */
        $label = $item['label'];
        unset($item['label']);

        $icon = $this->renderIcon($icon, $iconAttributes);

        if ($icon !== '') {
            $label = $icon . Span::tag()
                    ->content($label)
                    ->render();
        }

        $link = $url !== null
            ? A::tag()
                ->attributes($item)
                ->content($label)
                ->url($url)
                ->encode($encode)
                ->render() : $label;

        return strtr($template, ['{link}' => $link, '{label}' => $label, '{icon}' => $icon]);
    }

    /**
     * @psalm-return string[]
     */
    private function renderItems(): array
    {
        $renderItems = [];

        if ($this->homeItem !== null) {
            $renderItems[] = $this->renderItem($this->homeItem, $this->itemTemplate);
        }

        /** @psalm-var string[]|string $item */
        foreach ($this->items as $item) {
            if (!is_array($item)) {
                $item = ['label' => $item];
            }

            $renderItems[] = $this->renderItem(
                $item,
                isset($item['url']) ? $this->itemTemplate : $this->activeItemTemplate,
            );
        }

        return $renderItems;
    }
}
