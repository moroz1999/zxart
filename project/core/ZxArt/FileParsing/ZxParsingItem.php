<?php

namespace ZxArt\FileParsing;

abstract class ZxParsingItem
{
    protected string|null $path = null;
    protected string|null $itemName = null;
    protected string|null $content = null;
    /**
     * @var ZxParsingItem[]
     */
    protected array|null $items = null;
    protected string|null $parentMd5 = null;
    protected string|null $md5 = null;
    protected ZxParsingManager $zxParsingManager;

    public function __construct(ZxParsingManager $zxParsingManager)
    {
        $this->zxParsingManager = $zxParsingManager;
    }

    public function setParentMd5(string $parentMd5): void
    {
        $this->parentMd5 = $parentMd5;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getContent(): ?string
    {
        if (!$this->content && $this->path && is_file($this->path)) {
            $content = file_get_contents($this->path);
            if ($content) {
                $this->content = $content;
            }
        }
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return ZxParsingItem[]
     */
    public function getItems(): array
    {
        if ($this->items === null) {
            $this->parse();
        }
        return $this->items;
    }

    public function getMd5(): ?string
    {
        if (!$this->md5) {
            $this->md5 = md5($this->getContent());
        }
        return $this->md5;
    }

    public function getSize(): int
    {
        $content = $this->getContent();
        return $content ? strlen($content) : 0;
    }

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function getItemExtension(): string
    {
        if ($itemName = $this->getItemName()) {
            return strtolower(pathinfo($itemName, PATHINFO_EXTENSION));
        }
        return '';
    }

    public function setItemName(string $itemName): void
    {
        $this->itemName = $itemName;
    }

    public function getItemByName(string $itemName): self|null
    {
        if ($items = $this->items) {
            foreach ($items as $item) {
                if ($item->getItemName() === $itemName) {
                    return $item;
                }
            }
        }
        return null;
    }

    public function addItem(ZxParsingItemFile|ZxParsingItemRar|ZxParsingItemTrd|ZxParsingItemScl|ZxParsingItemTzx|ZxParsingItemTap|ZxParsingItemFolder|ZxParsingItemZip $item): void
    {
        $this->items = $this->items ?: [];
        $this->items[] = $item;
    }

    abstract public function getType(): string;

    abstract protected function parse(): void;

    /**
     * @param string[] $chain
     *
     * @psalm-param array<string> $chain
     */
    final public function getFileByChain(array $chain): self|null
    {
        $md5 = array_pop($chain);
        if ($md5 && ($this->getMd5() === $md5)) {
            if ($chain) {
                foreach ($this->getItems() as $item) {
                    if ($file = $item->getFileByChain($chain)) {
                        return $file;
                    }
                }
            } else {
                return $this;
            }
        }

        return null;
    }
}
