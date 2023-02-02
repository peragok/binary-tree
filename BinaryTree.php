<?php

/**
 * Класс реализует бинарное дерево без рекурсии
 */
class BinaryTree
{
    private array $tree = [];
    private Closure $closure;

    /**
     * Конструктор может принять в себя анонимную функцию.
     * Функция должна возвращать значение по которому будет происходить поиск.
     * Если функция не передана, то поиск будет происходить по переданным значениям
     *
     * @param Closure|null $closure
     */
    public function __construct(Closure $closure = null)
    {
        $this->closure = $closure ?? fn($x) => $x;
    }

    /**
     * Метод для массового заполнения дерева.
     *
     * @param array $array
     * @return $this
     */
    public function load(array $array): self
    {
        foreach ($array as $item) {
            $this->add($item);
        }
        return $this;
    }

    /**
     * Метод для добавления нового элемента в дерево.
     *
     * @param mixed $value
     * @return $this
     */
    public function add(mixed $value): self
    {
        if (count($this->tree) == 0) {
            $this->tree[0] = $value;
            return $this;
        }

        $realValue = $this->getRealValue($value);
        if (count($this->tree) == 1) {
            if ($this->getValueByKey(0) > $realValue) {
                $this->_add(0, $value);
                return $this;
            }
            $this->_add(1, $value);
            return $this;
        }

        $path = 0;
        for ($i = ceil(log(count($this->tree), 2)); $i >= 0; $i--) {
            $minKey = $this->getMinValueKey($i, $path);
            $maxKey = $this->getMaxValueKey($i, $path);

            if ($this->getValueByKey($minKey) >= $realValue) {
                $this->_add($minKey, $value);
                return $this;
            }

            if ($this->getValueByKey($maxKey) <= $realValue) {
                $this->_add($maxKey + 1, $value);
                return $this;
            }

            if (($maxKey - $minKey) <= 1) {
                $this->_add($maxKey, $value);
                return $this;
            }

            $path = $path << 1;
            $maxKeyNextDepth = $this->getMaxValueKey($i - 1, $path);
            if ($this->getValueByKey($maxKeyNextDepth) < $realValue) {
                $path++;
            }
        }

        return $this;
    }

    /**
     * Метод удаляет все элементы дерева по значению
     * @param $value
     * @return $this
     */
    public function delete($value): self
    {
        if ($keys = $this->findKeysByValue($value)) {
            foreach ($keys as $key) {
                $this->_delete($key);
            }
        }
        return $this;
    }

    /**
     * Метод возвращает все элементы дерева по переданному значению.
     * Если найдено несколько элементов, то вернется массив. Ключ массив является путем элемента в дереве, а значение является самим элементом.
     * Если найден только один элемент, то метод вернет этот элемент
     * Если элементов не найдено, то вернется Null
     *
     * @param $value
     * @return mixed
     */
    public function find($value): mixed
    {
        if ($keys = $this->findKeysByValue($value)) {
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $this->tree[$key];
            }

            if (count($result) === 1) {
                return array_shift($result);
            }
            return $result;
        }
        return null;
    }

    private function findKeysByValue($value): array
    {
        $treeCount = count($this->tree);
        
        if (count($this->tree) == 0 || $this->getValueByKey(0) > $value || $this->getValueByKey($treeCount - 1) < $value) {
            return [];
        }

        if (count($this->tree) <= 2) {
            $result = [];
            if ($this->getValueByKey(0) == $value) {
                $result[] = 0;
            }

            if ($this->getValueByKey(1) == $value) {
                $result[] = 1;
            }
            return $result;
        }

        $path = 0;
        $minFoundKey = null;
        $maxFoundKey = null;
        for ($i = ceil(log($treeCount, 2)); $i > 0; $i--) {
            if (!is_null($minFoundKey) && !is_null($maxFoundKey)) {
                break;
            }

            $minKey = $this->getMinValueKey($i, $path);
            $maxKey = $this->getMaxValueKey($i, $path);
            if ($minKey > $maxKey) {
                break;
            }

            if ($this->getValueByKey($minKey) == $value) {
                $minFoundKey = $minKey;
            }

            if ($this->getValueByKey($maxKey) == $value) {
                $maxFoundKey = $maxKey;
            }

            $path = $path << 1;
            $maxKeyNextDepth = $this->getMaxValueKey($i - 1, $path);
            if ($this->getValueByKey($maxKeyNextDepth) < $value) {
                $path++;
            }
        }

        $minRange = $minFoundKey ?? $maxFoundKey;
        for ($i = $minRange; $i <= $treeCount; $i++) {
            if ($i === $treeCount - 1) {
                return [$i];
            }

            if ($this->getValueByKey($i) == $value) {
                $maxRange = $i;
            } else {
                return range($minRange, $maxRange);
            }
        }

        return [];
    }

    private function getMinValueKey(int $depth, int $path): int
    {
        return $path << $depth;
    }

    private function getMaxValueKey(int $depth, int $path): int
    {
        $maxValue = $this->getMinValueKey($depth, $path) + ((1 << $depth) - 1);
        return isset($this->tree[$maxValue]) ? $maxValue : count($this->tree) - 1;
    }

    private function getValueByKey(int $key): mixed
    {
        return $this->getRealValue($this->tree[$key]);
    }

    private function getRealValue($value): mixed
    {
        $closure = $this->closure;
        return $closure($value);
    }

    private function _add(int $key, $value): void
    {
        for ($i = count($this->tree) - 1; $i >= $key; $i--) {
            $this->tree[$i + 1] = $this->tree[$i];
        }
        $this->tree[$key] = $value;
    }

    private function _delete(int $key): void
    {
        for ($i = $key; $i < count($this->tree) - 1; $i++) {
            $this->tree[$i] = $this->tree[$i + 1];
        }
        unset($this->tree[$i]);
    }
}