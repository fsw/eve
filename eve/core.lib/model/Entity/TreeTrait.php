<?php

/**
 * entity_Content.
 * 
 * @author fsw
 */

// TODO make this cool
// http://stackoverflow.com/questions/3623645/how-to-repair-a-corrupted-mptt-tree-nested-set-in-the-database-using-sql
// http://stackoverflow.com/questions/13016655/update-nested-sets-when-changing-parent-or-deleting
trait Entity_TreeTrait
{

    /** @Field_Relation(to='self') */
    public $parent;

    /** @Field_Int(hasFormInput=false) */
    public $left;

    /** @Field_Int(hasFormInput=false) */
    public $right;

    /** @Field_Int(hasFormInput=false) */
    public $level;

    /** @Field_Int(hasFormInput=false) */
    public $children_count;

    /** @Field_Float(hasFormInput=true) */
    public $order;

    public $children;

    public function treeName()
    {
        return str_repeat('&nbsp;&nbsp;', $this->level) . $this->__toString();
    }

    public static function getAll()
    {
        return self::getManyByQuery('ORDER BY `left`');
    }

    public static function getAllLeafs()
    {
        return self::getManyByQuery('WHERE `children_count` = 0 ORDER BY `left`');
    }

    public function getChildren()
    {
        return self::getManyByQuery('WHERE parent_id = ? ORDER BY `left`', [$this->id]);
    }

    public function getDescendants()
    {
        return self::getManyByQuery('WHERE `left` > ? AND `right` < ? ORDER BY `left`', [$this->left, $this->right]);
    }

    public function getLeafs()
    {
        return self::getManyByQuery('WHERE `left` > ? AND `right` < ? AND `children_count` = 0 ORDER BY `left`', 
                [$this->left, $this->right]);
    }

    public function getPath($include_self = true)
    {
        if ($this->parent->id) {
            $ret = $this->parent->getPath(true);
        } else {
            $ret = [];
        }
        if ($include_self) {
            $ret[] = $this;
        }
        return $ret;
    }

    private static function recTreeUpdate($id, $all, &$left, $level)
    {
        $root = ['id' => $id, 'left' => $left, 'level' => $level, 'children_count' => 0, 'children' => []];
        foreach ($all as $row) {
            if ($row['parent_id'] == $id) {
                $root['children_count'] ++;
                $left ++;
                $root['children'][] = self::recTreeUpdate($row['id'], $all, $left, $level + 1);
            }
        }
        $left ++;
        $root['right'] = $left;
        // SQL
        self::getDb()->query(
                'UPDATE `' . self::getTableName() .
                         '` SET `left` = ?, `right` = ?, `level` = ?, `children_count` = ?  WHERE id = ?;', 
                        [$root['left'], $root['right'], $root['level'], $root['children_count'], $root['id']]);
        return $root;
    }

    protected function postSaveTreeTrait($oldRow, $newRow)
    {
        if (empty($oldRow) or ($oldRow['parent_id'] != $this->parent->id) or ($oldRow['order'] != $this->order)) {
            $r = self::recTreeUpdate(0, 
                    self::getDb()->fetchAll(
                            'SELECT `id`, `parent_id`, `left`, `right`, `level` FROM `' . self::getTableName() .
                                     '` ORDER BY `order`;'), $left = 1, - 1);
            // var_dump($r);
            // die('updated?');
        }
    }

    protected function postSave($oldRow, $newRow)
    {
        $this->postSaveTreeTrait($oldRow, $newRow);
    }
}