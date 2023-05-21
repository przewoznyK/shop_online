# shopv2

Sklep internetowy - twórca Kamil Przewoźny

1. Opis projektu.
Jest to projekt sklepu internetowego, który pozwala użytkownikom na:
- rejestracje i logowanie
- dodawanie własnych produktów oraz sposób ich dostawy
- przeglądanie produktów, dodawanie ich do koszyka oraz zamawianie
- dodawanie ocen produktów i komentarzy
- przeglądanie profili innych użytkowników
- posiadanie wirtualnego portfela
- możliwość obsługi zamówień zarówno po stronie sprzedającego jak i kupującego
- wysyłanie wiadomości email gdy produkt jest gotowy do odbioru, wiadomość posiada:
> lokalizację produktu do odbioru,
> nazwę produktu,
> cenę produktu,
> liczbę sztuk,
> zdjęcie produktu,
> koszt dostawy,
> suma kosztów,
> link do informacji zwrotnej, w której użytkownik ma możliwość przesłania informacji do sprzedającego.


2. Wykorzystywane technologie
- framework Symfony
- PHP, JavaScript, JQuery, TWIG, CSS
- MySQL
- Bootstrap
- OpenLayers, Font Awesome


3. Ogólny opis stron

- index - Na tej stronie znajdują się wszystkie publiczne produkty użytkowników, których liczba sztuk jest większa od 0. Wyświetlane są nazwa, cena i zdjęcie produktu. Można kliknąć Check product", 
          aby zobaczyć szczegóły, lub dodać produkt do koszyka lub usunąć go z koszyka. Strona umożliwia wyszukiwanie produktów po nazwie, kategorii, cenie od-do, oraz sortowanie według ceny i popularności.

- check_product - Jest to strona umożliwiająca sprawdzenie szczegółów produktu takich jak:
> zdjęcia produktu,
> nazwę,
> ocenę użytkowników,
> ilość zamówień,
> zdjęcie i nazwę sprzedawcy, 
> cenę,
> opis,
> ilość sztuk,
> przycisk pozwalający dodać produkt do koszyka lub go usunąć,
> sposób dostawy.

Na stronie znajduje się również sekcja komentarzy do produktów. Komentarz zawiera:
> zdjęcie i nazwę użytkownika,
> ocena w postaci gwiazdek,
> datę dodania komentarza,
> treść komentarza,
> przyciski "like" i "dislike" (tylko zalogowani użytkownicy mogą oceniać komentarz),
> ikonę, która pozwala na usunięcie komentarza (tylko dla autora komentarza)

Aby dodać komentarz i ocenę produktu wystarczy być zalogowanym 

- register - Jak nazwa wskazuję, użytkownik może się na niej zarejestrować, wypełniając i przesyłając formularz w odpowiedni sposób zostaje wysłana wiadomość email na wpisany email użytkownika, 
             w której znajduje się link do weryfikacji. Po wejściu w ten link (jeśli token jest prawidłowy) konto użytkownika zostaje w bazie danych oznaczone jako zweryfikowane. Gdy rejestracja zakończy się pomyślnie
             stworzy się katalog użytkownika, w którym znajduje się katalog avatar, a w nim domyślny avatar, oraz katalog products, w którym będą zapisywane produkty użytkownika.

- login - Czyli strona, która pozwala na zalogowanie się.

- user - Strona użytkownika, domyślnie po wejściu na nią przekierowuje użytkownika na jego profil, a w nim znajduje się:
> nazwa,
> avatar użytkownika, który można zmienić klikając w niego i wybierając inne zdjęcie,
> opis, który również można zmienić,
> przycisk 'Submit', który zapisuje dwie powyższe zmiany,
> przycisk pozwalający na usunięcie konta,
> przycisk pozwalający na dodanie produktu
> wszystkie produkty użytkownika, także niepubliczne, wraz z 3 przyciskami dla każdego
Check
Edit 
Delete

Jeśli użytkownik przegląda nie swój profil
> nie będzie mógł edytować avatara i opisu użytkownika
> nie będzie miał dostępu do dodawania produktu
> produkty, które są ustawione na niepubliczne nie będą dla niego widoczne

- create_product - To tutaj dodaje się produkty, produkt zawiera:
> nazwę,
> cenę,
> ilość,
> kategorie,
> opis,
> czy jest publiczny,
> zdjęcia.
	
Zdjęcia są dodawane i wyświetlane asynchronicznie,trafiają one do specjalnego katalogu tymczasowego użytkownika i jego zawartość jest wyświetlana bez odświeżania strony, użytkownik ma możliwość dodania 
kolejnych zdjęć lub ich usunięcia za pomocą ikony X. Także jest możliwość wybrania zdjęcia głównego (jeśli użytkownik go nie wybierze zdjęciem głównym będzie pierwsze dodane zdjęcie produktu)
tzn. jest to zdjęcie które będzie odpowiadać za prezentacje produktu, np. na stronie głównej lub w wiadomości email. Zdjęcie główne trafia do tymczasowego katalogu użytkownika w katalogu main. 
Jeśli użytkownik zapisze produkt, nazwa katalogu zostanie zmieniona. Wszystkie zapisane informacje trafiają do bazy danych (także nazwa katalogu produktu). 
Jeśli użytkownik nie wrzuci żadnych zdjęć powstanie katalog produktu z domyślnym zdjęciem (zdjęcie domyślne również trafi main).

- add_delivery - Następny etap dodawania produktu, wybieramy w nim możliwości dostawy.
Można dodać 3 typy dostawy:
> odbiór osobisty: wymagane jest wpisanie lokalizacji, można wpisać dowolną ilość lokalizacji,
> paczkomat,
> kurier.
Dostawy są zapisywane w bazie danych, przy zakupie produktu zamawiający będzie miał możliwość wybrać jedną z nich.

- edit_product - Wyświetla takie same pola formularza jak w przypadku dodawania produktu ale są one już uzupełnione przez dane produktu, także zdjęcia z katalogu są wyświetlane i można je edytować jak w przypadku 
                 dodawania produktu. Gdy użytkownik zapisze dane, nadpiszą one wiersz produktu w tabeli. Następnie przejdzie ona na stronę add_delivery gdzie poprzednio dodane dostawy zostaną wyświetlone i
                 także będzie można je edytować.

- carts - W nim znajdują się dodane do koszyka produkty, są one podzielone zależnie od sprzedawcy, tzn. jeśli dwa produkty są od jednego sprzedawcy będą one w jednej ramce. 
          W jednym zamówieniu można wybrać kilka produktów pod warunkiem, że są one od tego samego sprzedawcy. Można usunąć produkt z koszyka za pomocą X. Aby zamówić dany produkt trzeba go zaznaczyć, 
          a później kliknąć buy aby rozpocząć tworzenie zamówienia

- buy_now - Mamy tutaj formularz:
> imię,
> nazwisko,
> email,
> numer telefonu,
> adres,
> komentarz do sprzedawcy.
	 
Produkty, które chcemy zamówić:
> zdjęcie,
> nazwę,
> cenę,
> ilość sztuk, którą możemy jeszcze na tym etapie zmienić,
> maksymalna ilość sztuk.
	   
Sposób dostawy: Są tu dostawy, które dostawca wprowadził, jeśli kupujący zamawia więcej niż jeden produkt, będzie miał możliwość wybrać te dostawy, które są dostępne dla wszystkich zamawianych produktów, tzn.
jeśli kupujący zamawia 2 produkty: jeden z nich ma dostawę kurier, a drugi kurier i paczkomat to będzie miał do wyboru tylko kuriera. Jeśli chodzi o odbiór osobisty to lokalizacja musi być identyczna dla obu
produktów aby była ona dostępna do wyboru dla tych produktów w zamówieniu.
> Wybierając odbiór osobisty w pole lokalizacji docelowej zostanie wpisany adres, który kupujący wybrał.
> Wybierając kuriera pole lokalizacji docelowej pobierze wartość z pola adres z formularza z danymi użytkownika ale będzie mógł zmienić tą wartość.
> Jeśli kupujący wybierze paczkomat, będzie musiał go wybrać z mapy, a jego lokalizacja zostanie wpisana do lokalizacji docelowej (zaznaczenie tej opcji uniemożliwia, zaznaczenie płatności przy odbiorze).

Metoda płatności:
> My wallet: czyli wirtualny portfel
> Płatność przy odbiorze.
	   
Przyciskiem Submit tworzymy zamówienie. 

- my_orders - znajdują się tam wszystkie zamówienia użytkownika, a w nich informacje:
> stan zamówienia,
> typ dostawy,
> lokalizacja docelowa,
> sposób płatności,
> cenę,
> czy zostało zapłacone,
> zdjęcie produktów, klikając na nie zostaniemy przekierowani na stronę produktu.
	      
Zamówienia podzielone są na 4 grupy:
In progess: znajdują się w nim statusy:
> pending - zamówienie powstało ale nie zostało one potwierdzone przez sprzedawcę
> progressing - zamówienie jest przygotowywane do wysyłki
Przy dwóch powyższych statusach zamówienie można nadal anulować (jeśli zamówienie zostało opłacone, pełna kwota wróci na konto kupującego)
> shipped - zamówienie zostało wysłane do kupującego
		
Ready to pick up: zamówienia gotowe do odbioru
> Your delivery is ready, check your email - oznacza to, że wiadomość email została wysłana
> Your delivery is ready to pick up. - oznacza to, że zamówienie jest gotowe do odbioru ale 
wiadomość email nie została wysłana.

CRON co 30 minut uruchamia komende, która sprawdza status zamówienia, jeśli status to: ready_to_pick_up do zamawiającego jest wysyłana wiadomość email, sprawdza ona również różnicę w 
czasie pomiędzy uruchomieniem komendy, a zamówień o status shipped, jeśli różnica wynosi przynajmniej 15 minut, zamówienia te również trafiają do grupy Ready to pick up i wiadomość email zostaje wysłana.

Dałem możliwość aby uruchomić tą komendę za pomocą przycisku 'Send email', pojawi się on, gdy status przesyłki będzie wynosić: Your delivery is ready to pick up.
Problem: trafiają tam zamówienia, które zostały oznaczone przez kupującego w wiadomości zwrotnej inną opcją niż 'Everything ok'

Complete: trafiają tam zamówienia, które zostały oznaczone przez kupującego w wiadomości zwrotnej opcją 'Everything ok'
	      
- my_sell - znajdują się tutaj zamówienia produktów, które użytkownik chce sprzedać, produkty wyświetlane są w taki sam sposób jak na stronie my_orders. 
Zamówienia są podzielone na 6 grup: 

> Waiting for accept: sprzedawca może zaakceptować lub odrzucić zamówienie, jeśli je odrzuci zamówienie zostanie usunięte, jeśli je zaakceptuje u kupującego status tego zamówienia zmieni się na progressing.
> In progress: Nadal sprzedawca ma możliwość anulowania zamówienia ale ma także możliwość zmienienia statusu zamówienia na shipped (jeśli sposób dostawy to kurier lub paczkomat), informując przy tym kupującego,
  że jego przesyłka została wysłana lub na Ready to pick up gdy kupujący wybrał opcję osobistego odbioru.

Jeśli zamawiający anulował zamówienie, a zostało ono opłacone, to środki wrócą na konto kupującego.
Jeśli sprzedawca zmienił status na shipped lub Ready to pick up środki trafią do jego wirtualnego portfela (jeśli zamówienie zostało opłacone)

> Shipped: czyli zamówienie jest wysłane do kupującego
> Waiting for feedback: czyli gdy zamówienie dotarło do miejsca docelowego ale użytkownik nie przesłał jeszcze informacji zwrotnej
> Problem: trafiają tam zamówienia, które zostały oznaczone przez kupującego w wiadomości zwrotnej inną opcją niż 'Everything ok'.

> Complete: trafiają tam zamówienia, które zostały oznaczone przez kupującego w wiadomości zwrotnej opcją 'Everything ok'.

- my_wallet - na tej stronie można dodać się $ do swojego konta, oczywiście nie są to prawdziwe pieniądze, ma to tylko symulować wirtualny portfel.
