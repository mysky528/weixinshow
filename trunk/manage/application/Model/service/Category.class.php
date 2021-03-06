<?php

/**
 * 后台文章栏目模块 Model
 * ============================================================================
 * TBlog TBlog博客系统　BY Tmac PHP MVC framework
 * $Author: zhangwentao $  <zwttmac@qq.com>
 * $Id: Category.class.php 6 2014-09-20 15:13:57Z zhangwentao $
 * http://www.t-mac.org；
 */
class service_Category_manage extends service_Category_base
{
    /**
     * 初始化变量　定义私有变量
     */
    public function _init()
    {
        
    }

    /**
     * del
     * @param int $class_id
     */
    public function deleteCategoryById($cat_id)
    {
        $entity_Category_base = new entity_Category_base();
        $entity_Category_base->category_delete = 1;
        $dao = dao_factory_base::getCategoryDao();
        $dao->setPk($cat_id);
        return $dao->updateByPk($entity_Category_base);
    }

    /**
     * 取无限分类
     * @global type $category_list
     * @param type $arr
     * @param type $parent
     * @param type $url
     * @param type $username
     * @param type $indexurl
     * @return string 
     */
    public function fenlei($arr, $parent)
    {
        global $category_list; //定义全局变量 返回值        
        $BASE_V = BASE_V . $GLOBALS['TmacConfig']['Template']['template_dir'] . '/';
        $num = count($arr);

        for ($i = 0; $i < $num; $i++) {//循环该层
            if ($i == 0) {
                for ($j = 0; $j < $num; $j++) {
                    if ($arr[$j]->cat_pid == $parent) {
                        $category_list .= '<ul class="category_list" style="padding:0px">';
                        break;
                    }//有元素的该层开始是输出<ul>，
                }
            }
            if ($arr[$i]->cat_pid == $parent) {  //层中符合父级id的元素输出                 
                $arr_array = $arr[$i];
                $category_content = Functions::cut_str(strip_tags($arr_array->category_content), 42);
                $category_content = empty($category_content) ? '&nbsp;' : $category_content;
                $category_list .= "<li><dl>                
                    <dd class='cl_one'><div class='cate'><img src=\"{$BASE_V}image/desc.gif\" title='展开/收缩'><a href=\"" . PHP_SELF . "?m=archives.arclist&channelid=" . $arr_array->channeltype . "&cat_id=" . $arr_array->cat_id . "\">" . $arr_array->cat_name . "</a></div></dd>
                    <dd class='cl_two'>" . $arr_array->category_nicename . "</dd>
                    <dd class='cl_three'>$category_content</dd>                            
                    <dd class='cl_four'><a href=\"" . PHP_SELF . "?m=archives.catgoto&channelid=" . $arr_array->channeltype . "&cat_id=" . $arr_array->cat_id . "\">发布内容</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"" . PHP_SELF . "?m=archives.arclist&channelid=" . $arr_array->channeltype . "&cat_id=" . $arr_array->cat_id . "\">查看内容</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"" . PHP_SELF . "?m=category.add&cat_id=" . $arr_array->cat_id . "\">修改</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick=\"{if(confirm('删除将包括该分类的所有信息，确定删除吗?')){return true;}return false;}\"href=\"" . PHP_SELF . "?m=category.del&cat_id=" . $arr_array->cat_id . "\">删除</a></dd></dl>";
                $this->fenlei($arr, $arr[$i]->cat_id);  //递归执行寻找当前元素的子元素，这些子元素<ul><li>当前元素<ul><li>子元素</li></ul></li></ul>
                $category_list .= "</li>";      //该</li>放在fenlei函数后是为了把形成该元素的子元素也要包含在该元素的<li>内
            }
            if ($i == ($num - 1)) {
                for ($j = 0; $j < $num; $j++) {
                    if ($arr[$j]->cat_pid == $parent) {
                        $category_list .= "</ul>";
                        break;
                    }//闭合ul
                }
            }
        }
        return $category_list;
    }

    /**
     * 取Select list
     * @global string $category_tree_list
     * @param type $parent
     * @param type $uid
     * @param type $current_id
     * @return string 
     */
    public function getCategoryTreeList($parent, $current_id)
    {        
        $catetory_array = $this->getCategoryArray();        
        global $category_tree_list;
        $category_tree_list = $this->tree_fenlei($catetory_array, $parent, 0, $current_id);
        return $category_tree_list;
    }

    /**
     * 递归取select option
     * @global string $category_tree_list
     * @param type $arr
     * @param type $parent
     * @param type $level
     * @param type $current_id
     * @return string 
     */
    public function tree_fenlei($arr, $parent, $level, $current_id)
    {
        global $category_tree_list; //定义全局变量 返回值        
        $BASE_V = BASE_V;
        $num = count($arr);
        $prefix_space = '';
        for ($j = 0; $j < $level; $j++) {
            $prefix_space .= "　"; //第一级就增加一个缩进            
        }

        for ($i = 0; $i < $num; $i++) {//循环该层
            if ($arr[$i]->cat_pid == $parent) {  //层中符合父级id的元素输出                 
                $arr_array = $arr[$i];
                if ((int) $arr_array->cat_id === (int) $current_id) {
                    $select = " selected";
                } else {
                    $select = "";
                }
                $category_tree_list .= '<option value="' . $arr_array->cat_id . '" ' . $select . '>' . $prefix_space . '|-' . $arr_array->cat_name . '</option>';
                $this->tree_fenlei($arr, $arr[$i]->cat_id, $level + 1, $current_id);  //递归执行寻找当前元素的子元素，这些子元素<ul><li>当前元素<ul><li>子元素</li></ul></li></ul>                
            }
        }
        return $category_tree_list;
    }

    /**
     * 判断category_nicename唯一性的
     * @param type $category_nicename
     * @return type 
     */
    public function checkCategoryNicename($category_nicename)
    {
        $dao = dao_factory_base::getCategoryDao();
        $where = "category_nicename = '{$category_nicename}'";
        $dao->setField('cat_id');
        $dao->setWhere($where);
        $res = $dao->getInfoByWhere();
        if ($res) {
            return $res->cat_id;
        } else {
            return $res;
        }
    }

    /**
     * 取栏目分类的所属模型数组
     * @return type 
     */
    public function getCategoryChannel()
    {
        $dao = dao_factory_base::getCategoryDao();
        $dao->setField('cat_id,channeltype');
        $res = $dao->getListByWhere();

        $result_array = array();
        if ($res) {
            foreach ($res AS $k => $v) {
                $result_array[$v->cat_id] = $v->channeltype;
            }
        }
        return $result_array;
    }

}