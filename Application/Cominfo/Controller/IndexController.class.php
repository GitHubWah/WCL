<?php
namespace Cominfo\Controller;

use Think\Controller;
use Think\Upload;

class IndexController extends Controller
{
    public function index()
    {
        $user_id = $_SESSION['sess_wcl']['company_id'];
        if (isset($_SESSION['sess_wcl']['company_auth']) && $_SESSION['sess_wcl']['company_auth']) {
            /*
             * 获取：简历推荐
             */

            $cominfo = M('cominfo');
            $industryinfo = $cominfo->where(['companyuser_id' => $user_id])->field('industry_id')->select();
            $industryinfo_id = $industryinfo[0]['industry_id'];
            $user = M('user');
            $cv = M('cv');
            $job = M('job');
            $this->job_show = $job
                ->where(['wcl_job.sindustry_id' => $industryinfo_id])
                ->join('left join wcl_cv on wcl_job.cv_id = wcl_cv.id')
                ->join('left join wcl_industry ON wcl_industry.id = wcl_job.sindustry_id')
                ->field('wcl_cv.id,wcl_industry.name as industry_name,wcl_cv.realname,wcl_cv.updated_at')
                ->limit(4)
                ->select();
            /**
             * 获取行业信息
             */
            $industry = M('industry');
            $this->indsutry_list = $industry->field('id,name')->order('id')->select();

            /*
             * 获取学历
             */
            $degree = M('degree');
            $this->degree_list = $degree->field('id,name')->order('id')->select();

            if ($user_id) {
                /**
                 * 取companyinfo_id
                 */
                $company = M('Company');
                $cominfo = $company
                    ->where(['wcl_cominfo.companyuser_id' => $user_id])
                    ->join('left join wcl_cominfo on wcl_company.id=wcl_cominfo.companyuser_id')
                    ->select();
                $post = M('post');
                $this->post_list = $post
                    ->where(['wcl_post.companyinfo_id' => $cominfo[0]['id']])
                    ->join('left join wcl_function on wcl_post.function_id=wcl_function.id')
                    ->field('wcl_post.id,wcl_post.updated_at,wcl_function.name as function_name')
                    ->select();

                $this->display();
            }

        } else {
            /**
             * 没有session，说明根本没有登录，让他去登录页
             */
            $this->error('您还没有登录，无法访问该网页！', '/Company/Index/login');
        }

    }

    /**
     * 到企业基本信息新增和职位新增页面
     */
    public function create()
    {
        $this->display();
    }

    /**
     * 企业基本信息创建
     */
    public function comInfoAdd()
    {
        $company_id = $_SESSION['sess_wcl']['company_id'];
        if ($company_id) {
            $cv = M('cominfo');
            $cominfo = $cv->where(['companyuser_id' => $company_id])->field('id,name')->select();
            if ($cominfo) {
                /**
                 * 已经创建过企业基本信息
                 */
                $this->error('您已经创建创建企业基本信息，不能重复创建');
            } else {
                /**
                 * 获取公司性质表信息
                 */
                $companytype = M('companytype');

                $this->companytype_list = $companytype->field('id,name')->order('id')->select();

                /**
                 * 省份列表
                 */
                $province = M('province');
                $this->province_list = $province->field('id,name')->select();
                /**
                 * 获取行业信息
                 */
                $industry = M('industry');
                $this->indsutry_list = $industry->field('id,name')->order('id')->select();
                $this->display();
            }

        } else {
            /**
             * 没有session，说明根本没有登录，让他去登录页
             */
            $this->error('您还没有登录，无法访问该网页！', '/Company/Index/login');
        }
    }

    /***
     * ajax方式获取行业对应的职位
     * @param $industry_id
     */
    public function getFunction($industry_id)
    {
        /**
         * ajax请求传过来的行业id,,为职位表的外键
         */
        $industry_id = I('post.industry_id');
        /**
         * 获取职位信息
         */
        $function = M('function');
        $function_list = $function->where(['industry_id' => $industry_id])->field('id,name')->order('id')->select();
        /**
         * 转换成json格式返回
         */
        $this->ajaxReturn($function_list);
    }

    /**
     * 企业基本信息的保存
     */
    public function comInfoSave()
    {
        /**
         * 对上传文件进行处理
         */
        $upload = new Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
        $upload->rootPath = C('TMPL_PARSE_STRING')['UPLOAD_PATH'] . 'companys/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        } else {// 上传成功
            /**
             * 上传成功再对表单进行处理
             */
            $cominfo = D("cominfo"); // 实例化User对象
            if (!$cominfo->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($cominfo->getError());
            } else {
                // 验证通过 可以进行其他数据操作
                $cominfo->imagename = $info['photo']['name'];
                $cominfo->imagepath = $info['photo']['savepath'] . $info['photo']['savename'];
                $result = $cominfo->add();
                if ($result) {
                    $this->success('基本信息保存成功', '/Cominfo/index/create');
                } else {
                    $this->error('写入错误');
                }
            }
        }
    }

    /**
     * 对职位进行操作前预览
     */
    public function editPost()
    {
        $user_id = $_SESSION['sess_wcl']['company_id'];
        if ($user_id) {
            /**
             * 取companyinfo_id
             */
            $company = M('Company');
            $cominfo = $company
                ->where(['wcl_cominfo.companyuser_id' => $user_id])
                ->join('left join wcl_cominfo on wcl_company.id=wcl_cominfo.companyuser_id')
                ->select();
            $post = M('post');
            $this->post_list = $post
                ->where(['wcl_post.companyinfo_id' => $cominfo[0]['id']])
                ->join('left join wcl_function on wcl_post.function_id=wcl_function.id')
                ->join('left join wcl_salary on wcl_post.salary_id=wcl_salary.id')
                ->join('left join wcl_worktime on wcl_post.worktime_id=wcl_worktime.id')
                ->field('wcl_post.id,wcl_post.created_at,wcl_function.name as function_name,wcl_salary.cash as salary_cash,wcl_worktime.name as worktime_name')
                ->select();
            $this->display();
        }
    }
}