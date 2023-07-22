<?php
/*
Plugin Name: Wahlbezirk abfragen
Description: Ein einfaches Plugin, das eine Abfrageformular mit Strasse und Hausnummer erstellt und mit einem entsprechenden Strassenverzeichnis in einer Datenbank abgleicht.
Version: 1.0
Author: Falk Krüger
*/

function wahlbezirk_abfragen_form() {
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<p>';
    echo 'Strasse <br/>';
    echo '<input type="text" name="strasse" pattern="[a-zA-Z0-9 äöüÄÖÜß]+" value="' . ( isset( $_POST["strasse"] ) ? esc_attr( $_POST["strasse"] ) : '' ) . '" size="40" required />';
    echo '</p>';
    echo '<p>';
    echo 'Hausnummer (optional) <br/>';
    echo '<input type="text" name="hausnummer" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["hausnummer"] ) ? esc_attr( $_POST["hausnummer"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p><input type="submit" name="submit" value="Wahlbezirk(e) abfragen"></p>';
    echo '</form>';
}

function wahlbezirk_abfragen() {
    if ( isset( $_POST['submit'] ) ) {
        $strasse = $_POST["strasse"];
        $hausnummer = $_POST["hausnummer"];

        // Datenbankverbindung herstellen
        $servername = "[servername]";
        $username = "[username]";
        $password = "[password]";
        $dbname = "[dbname]";

        // Erstellen Sie eine Verbindung
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Überprüfen Sie die Verbindung
        if ($conn->connect_error) {
            die("Verbindung fehlgeschlagen: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM Strassenverzeichnis WHERE Strasse LIKE '%$strasse%'";

        if(!empty($hausnummer)) {
            $sql .= " AND Hausnummer_von <= $hausnummer AND Hausnummer_bis >= $hausnummer";
            if($hausnummer % 2 == 0) {
                $sql .= " AND Hausnummer_von % 2 = 0";
            } else {
                $sql .= " AND Hausnummer_von % 2 = 1";
            }
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<p>Es wurde" . ($result->num_rows > 1 ? "n " : " ") . $result->num_rows . " Datensatz" . ($result->num_rows > 1 ? "e" : "") . " gefunden:</p>";
            echo "<table>";
            echo "<tr><th>Strasse</th><th>Hausnummern</th><th>Wahlbezirk</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["Strasse"]. "</td><td>" . $row["Hausnummer_von"]. "-" . $row["Hausnummer_bis"]. "</td><td>" . $row["Wahlbezirk"]. "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Keine Ergebnisse gefunden";
        }
        $conn->close();
    }
}

function wahlbezirk_abfragen_shortcode() {
    ob_start();
    wahlbezirk_abfragen_form();
    wahlbezirk_abfragen();

    return ob_get_clean();
}

add_shortcode( 'wahlbezirk_abfragen', 'wahlbezirk_abfragen_shortcode' );

?>
