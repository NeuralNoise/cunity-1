<?php

/**
 * ########################################################################################
 * ## CUNITY(R) V2.0 - An open source social network / "your private social network"     ##
 * ########################################################################################
 * ##  Copyright (C) 2011 - 2015 Smart In Media GmbH & Co. KG                            ##
 * ## CUNITY(R) is a registered trademark of Dr. Martin R. Weihrauch                     ##
 * ##  http://www.cunity.net                                                             ##
 * ##                                                                                    ##
 * ########################################################################################.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or any later version.
 *
 * 1. YOU MUST NOT CHANGE THE LICENSE FOR THE SOFTWARE OR ANY PARTS HEREOF! IT MUST REMAIN AGPL.
 * 2. YOU MUST NOT REMOVE THIS COPYRIGHT NOTES FROM ANY PARTS OF THIS SOFTWARE!
 * 3. NOTE THAT THIS SOFTWARE CONTAINS THIRD-PARTY-SOLUTIONS THAT MAY EVENTUALLY NOT FALL UNDER (A)GPL!
 * 4. PLEASE READ THE LICENSE OF THE CUNITY SOFTWARE CAREFULLY!
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program (under the folder LICENSE).
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * If your software can interact with users remotely through a computer network,
 * you have to make sure that it provides a way for users to get its source.
 * For example, if your program is a web application, its interface could display
 * a "Source" link that leads users to an archive of the code. There are many ways
 * you could offer source, and different solutions will be better for different programs;
 * see section 13 of the GNU Affero General Public License for the specific requirements.
 *
 * #####################################################################################
 */

namespace Cunity\Likes\Models;

use Cunity\Core\Request\Post;
use Cunity\Core\View\Ajax\View;
use Cunity\Gallery\Models\Process;

/**
 * Class Likes.
 */
class Likes
{
    /**
     * @var Db\Table\Likes
     */
    private $table;

    /**
     * @var View
     */
    private $view;

    /**
     * @param $action
     */
    public function __construct($action)
    {
        $this->view = new View();
        if (Post::get('ref_name') === null || Post::get('ref_id') === null) {
            $this->view->setStatus(false);
        } else {
            $this->table = new Db\Table\Likes();
            if (method_exists($this, $action)) {
                call_user_func([$this, $action]);
            }
        }
        $this->view->sendResponse();
    }

    /**
     *
     */
    private function like()
    {
        $res = $this->table->like(Post::get('ref_id'), Post::get('ref_name'));
        $this->view->setStatus($res !== false);
        if (Post::get('ref_name') == 'image') {
            new Process('loadImage', Post::get('ref_id'));
        }
    }

    /**
     *
     */
    private function dislike()
    {
        $res = $this->table->dislike(Post::get('ref_id'), Post::get('ref_name'));
        $this->view->setStatus($res !== false);
        if (Post::get('ref_name') === 'image') {
            new Process('loadImage', Post::get('ref_id'));
        }
    }

    /**
     *
     */
    private function unlike()
    {
        $res = $this->table->unlike(Post::get('ref_id'), Post::get('ref_name'));
        $this->view->setStatus($res !== false);
        if (Post::get('ref_name') == 'image') {
            new Process('loadImage', Post::get('ref_id'));
        }
    }

    /**
     *
     */
    private function get()
    {
        $res = $this->table->getLikes(Post::get('ref_id'), Post::get('ref_name'), Post::get('dislike'));
        $this->view->setStatus($res !== false);
        $this->view->addData(['likes' => $res]);
    }
}
