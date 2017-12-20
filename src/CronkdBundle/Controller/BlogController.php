<?php
namespace CronkdBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/blog")
 */
class BlogController extends Controller
{
    /**
     * @Route("", name="blog")
     * @Template("CronkdBundle:Blog:index.html.twig")
     */
    public function indexAction() {
        return;
    }

    /**
     * @Route("/{id}", name="blog_post")
     * @Template("CronkdBundle:Blog:post.html.twig")
     */
    public function postAction() {
        $body = <<<EOT
<p>Welcome to the <strong>CronKD Blog</strong>.  This is our first post.</p>
<p>The CronKD Blog is a convenient place for our development team to make special announcements.</p>
<h2>What is CronKD?</h2>
<p>CronKD is a web-based, mobile-friendly, minimal-UI, quick-play, persistent-world, tactical/strategic, economy and war (PVP), tech-tree/upgrades, kingdom-builder, MMORPG.</p>
<h2>Getting Started</h2>
<ol>
<li>Register a User.</li>
<li>Join the World.</li>
<li>Build your Kingdom.</li>
</ol>
<h2>How to Play?</h2>
<p>There are many ways to play.  Imho atm, game play revolves around (1) developing and maintaining your economy and (2) finding good targets and successfully attacking.</p>
<h3>1. Economy</h3>
<p>At first, you will use your civilians to gather material and build houses.  Later, you will have upgraded units accessible to you.  You will gather special material and build better buildings.</p>
<h3>2. Military</h3>
<p>You will convert some civilians to soldiers and spies.  Soldiers will defend your kingdom and execute attacks.  You will use spies to get information about other kingdoms.  Later, you will have upgraded units accessible to you.  You will train stronger specialized military units.</p>
<h2>Strategy</h2>
<p>Early on, your choices will be limited.  You can get by simply building houses, gathering materials, spying on other kingdoms, and attacking with a few measely soldiers.  Pretty soon, you will want to upgrade and develop more complex economic and military strategies.  Be sure to read the Help pages for technical details and strategic insights.</p>
<h2>glhf</h2>
<p>Thanks for playing CronKD!  Please share your feedback and help spread the word.  We appreciate your support!  Good luck and have fun!</p>
<br>
<p>Sincerely,</p>
<p><strong>Entropy</strong> and the CronKD Development Team</p>
EOT;


        $post = (object) [
            'title' => "Hello World",
            'date' => new \DateTime("2017-12-20"),
            'body' => $body
        ];

        return [
            'post' => $post,
        ];
    }
}