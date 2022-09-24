<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{
    //Session interface avec symfony 6
    private $stack;

    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack->getCurrentRequest(); // on récupere la requete courante
    }

    // Ajouter un élément au panier
    public function add($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
    }

    public function get()
    {
        $methodget = $this->stack->getSession();
        return $methodget->get('cart');
    }

    // Supprimer un élément du panier
    public function delete($id)
    {
        $cart = $this->stack->getSession()->get('cart', []);

        unset($cart[$id]);

        return $this->getSession()->set('cart', $cart);
    }

    public function remove()
    {
        return $this->session->set('cart', []);
    }
}
