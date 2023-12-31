<?php

declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 *
 * PHP Version 7.4
 *
 * @file     PageTemplate.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.1
 */

// to do: change name 'PageTemplate' throughout this file
require_once './Page.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Bestellung extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks

    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
     * @return array An array containing the requested data. 
     * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData(): array
    {
        // to do: fetch data for this view from the database
        // to do: return array containing data
        $article_list = array();
        $sql = "SELECT a.article_id, a.name, a.price FROM article a";
        $recordset = $this->_database->query($sql);
        if (!$recordset) throw new Exception("Fehler in Abfrage: " . $this->_database->error);

        // read selected records into result array
        while ($record = $recordset->fetch_assoc()) {
            $article_id = $record["article_id"];
            $pizza_name = $record["name"];
            $price = $record["price"];
            array_push($article_list, array($article_id, $pizza_name, $price));
        }
        $recordset->free();
        return $article_list;
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available- the content of
     * all views contained is generated.
     * Finally, the footer is added.
     * @return void
     */
    protected function generateView(): void
    {
        $data = $this->getViewData();
        $this->generatePageHeader('Pizza Service'); //to do: set optional parameters

        for ($i = 0; $i < count($data); $i++) {
            $pizza_name = $data[$i][1];
            $price = $data[$i][2];
            echo <<< HTML
            <section>
            <img
                width="100"
                height="100"
                src="../images/41J3qSlgJiL.jpg"
                alt=$pizza_name
            />
            <h2>$pizza_name</h2>
            <h3>$ $price</h3>
            </section>\n
            HTML;
        }

        echo <<< HTML
        <section>
        <form action="bestellung.php" method="post" >
            <h1>Warenkorb</h1>
            <select tabindex="1" name="pizza[]" multiple>
            <option selected value="1" id="pizza1">Salami</option>
            <option value="2" id="pizza2">Vegetaria</option>
            <option value="3" id="pizza3">Spinat Huehnchen</option>
            </select>
            <input name="Adresse" type="text" value="" placeholder="ihre Adresse" >
            <button tabindex="2" accesskey="l">Alle Loeschen</button>
            <button tabindex="3" accesskey="a">Auswahl Loeschen</button>
            <input  tabindex="4" type="submit" accesskey="b" value="Bestellen" >
        </form>
        </section>
        HTML;
        // to do: output view of this page
        $this->generatePageFooter();
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
     * @return void
     */
    protected function processReceivedData(): void
    {
        parent::processReceivedData();
        // to do: call processReceivedData() for all members
        //make new user
        if (isset($_POST["pizza"]) && isset($_POST["Adresse"])) {
            $ordering_id = $this->_database->insert_id;
            $escaped_ordering_id = $this->_database->real_escape_string($ordering_id);
            $address = $_POST["Adresse"];
            $escaped_address = $this->_database->real_escape_string($address);
            $sql = "INSERT INTO ordering (ordering_id ,address) VALUES ('$escaped_ordering_id' , '$escaped_address')";
            $recordset = $this->_database->query($sql);
            if (!$recordset) throw new Exception("Fehler in Abfrage: " . $this->_database->error);
            //make new order
            $sql = "SELECT ordering_id FROM ordering ORDER BY ordering_time DESC LIMIT 1";
            $recordset = $this->_database->query($sql);
            // read selected records into result array
            while ($record = $recordset->fetch_assoc()) {
                $ordering_id = $record["ordering_id"];
            }
            $recordset->free();
            //insert pizza
            $pizzas = $_POST["pizza"];
            for ($i = 0; $i < count($pizzas); $i++) {
                $escaped_pizza = $this->_database->real_escape_string($pizzas[$i]);
                $sql = "INSERT INTO `ordered_article`(`ordered_article_id`, `ordering_id`, `article_id`, `status`) VALUES ('0','$escaped_ordering_id','$escaped_pizza','0')";
                $recordset = $this->_database->query($sql);
                if (!$recordset) throw new Exception("Fehler in Abfrage: " . $this->_database->error);
            }
        }
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
     * @return void
     */
    public static function main(): void
    {
        try {
            $page = new Bestellung();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Bestellung::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >