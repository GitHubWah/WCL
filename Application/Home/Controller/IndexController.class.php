<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        /**
         * ����ҳ�����������
         * 1����һ�ν���ҳ��˭�����Խ�
         * 2����¼֮������ҳ��ַ
         * 3���˳�֮��������һ���û�������ҵ�û��˳�������ҳ
         */
        if (isset($_SESSION['sess_wcl']['is_auth']) && $_SESSION['sess_wcl']['is_auth']) {
            /**
             * ����ͨ�û���¼֮�������ҳ������ģ���û���˳�����ʱ���õ�session�����name��ʾ��ҳ����
             */
            $this->username = $_SESSION['sess_wcl']['username'];
            $this->mark = 'user';
        } else {
            if (isset($_SESSION['sess_wcl']['company_auth']) && $_SESSION['sess_wcl']['company_auth']) {
                /**
                 * ����ͨ�û���¼֮�������ҳ������ģ���û���˳�����ʱ���õ�session�����name��ʾ��ҳ����
                 */
                $this->username = $_SESSION['sess_wcl']['username'];
                $this->mark = 'company';
            } else {
                /**
                 * �ǵ�¼״̬��ֵΪ����
                 */
                $this->username = '';
                $this->mark = '';
            }
        }

        $this->display();
    }
}