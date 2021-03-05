<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$mod_strings = array (
    //DON'T CONVERT THESE THEY ARE MAPPINGS
    'db_last_name' => 'LBL_LIST_LAST_NAME',
    'db_first_name' => 'LBL_LIST_FIRST_NAME',
    'db_title' => 'LBL_LIST_TITLE',
    'db_email1' => 'LBL_LIST_EMAIL_ADDRESS',
    'db_account_name' => 'LBL_LIST_ACCOUNT_NAME',
    'db_email2' => 'LBL_LIST_EMAIL_ADDRESS',

    //END DON'T CONVERT

    // Dashboard Names
    'LBL_LEADS_LIST_DASHBOARD' => 'Instrumentpanel med lista över möjliga kunder',
    'LBL_LEADS_RECORD_DASHBOARD' => 'Instrumentpaneler med poster över möjliga kunder',
    'LBL_LEADS_FOCUS_DRAWER_DASHBOARD' => 'Fokuslåda för möjliga kunder',

    'ERR_DELETE_RECORD' => 'Ett objektnummer måste specificeras för att kunna radera leadet.',
    'LBL_ACCOUNT_DESCRIPTION'=> 'Organisationsbeskrivning',
    'LBL_ACCOUNT_ID'=>'Konto-ID',
    'LBL_ACCOUNT_NAME' => 'Organisationsnamn:',
    'LBL_ACTIVITIES_SUBPANEL_TITLE'=>'Aktiviteter',
    'LBL_ADD_BUSINESSCARD' => 'Lägg till visitkort',
    'LBL_ADDRESS_INFORMATION' => 'Adressinformation',
    'LBL_ALT_ADDRESS_CITY' => 'Alternativ adress stad:',
    'LBL_ALT_ADDRESS_COUNTRY' => 'Alternativ adress land:',
    'LBL_ALT_ADDRESS_POSTALCODE' => 'Alternativ adress postnummer:',
    'LBL_ALT_ADDRESS_STATE' => 'Alternativ adress stat:',
    'LBL_ALT_ADDRESS_STREET_2' => 'Alternativ adress gata 2:',
    'LBL_ALT_ADDRESS_STREET_3' => 'Alterantiv adress gata 3:',
    'LBL_ALT_ADDRESS_STREET' => 'Alternativ adress gata:',
    'LBL_ALTERNATE_ADDRESS' => 'Annan adress:',
    'LBL_ANY_ADDRESS' => 'Någon adress:',
    'LBL_ANY_EMAIL' => 'Email:',
    'LBL_ANY_PHONE' => 'Någon telefon:',
    'LBL_ASSIGNED_TO_NAME' => 'Tilldelad till',
    'LBL_ASSIGNED_TO_ID' => 'Tilldelad till användare:',
    'LBL_BACKTOLEADS' => 'Tillbaka till leads',
    'LBL_BUSINESSCARD' => 'Konvertera lead',
    'LBL_CITY' => 'Stad:',
    'LBL_CONTACT_ID' => 'Kontakt ID',
    'LBL_CONTACT_INFORMATION' => 'Översikt',
    'LBL_CONTACT_NAME' => 'Lead namn:',
    'LBL_CONTACT_OPP_FORM_TITLE' => 'Lead-Affärsmöjlighet',
    'LBL_CONTACT_ROLE' => 'Roll:',
    'LBL_CONTACT' => 'Möjlig kund:',
    'LBL_CONVERT_BUTTON_LABEL' => 'Konvertera',
    'LBL_SAVE_CONVERT_BUTTON_LABEL' => 'Spara och konvertera',
    'LBL_CONVERT_PANEL_OPTIONAL' => '(frivilligt)',
    'LBL_CONVERT_ACCESS_DENIED' => 'Du saknar redigerings tillgång till de moduler som krävs för att omvandla en lead: {{requiredModulesMissing}}',
    'LBL_CONVERT_FINDING_DUPLICATES' => 'Söka efter dubbletter...',
    'LBL_CONVERT_IGNORE_DUPLICATES' => 'Ignorera och skapa nya',
    'LBL_CONVERT_BACK_TO_DUPLICATES' => 'Tillbaka till dubbletter',
    'LBL_CONVERT_SWITCH_TO_CREATE' => 'Skapa ny',
    'LBL_CONVERT_SWITCH_TO_SEARCH' => 'Sök',
    'LBL_CONVERT_DUPLICATES_FOUND' => '{{duplicateCount}} dubbletter funna',
    'LBL_CONVERT_CREATE_NEW' => 'Nytt {{moduleName}}',
    'LBL_CONVERT_SELECT_MODULE' => 'Välj {{moduleName}}',
    'LBL_CONVERT_SELECTED_MODULE' => 'Väljer {{moduleName}}',
    'LBL_CONVERT_CREATE_MODULE' => 'Skapa {{moduleName}}',
    'LBL_CONVERT_CREATED_MODULE' => 'Skapar {{moduleName}}',
    'LBL_CONVERT_RESET_PANEL' => 'Återställ',
    'LBL_CONVERT_COPY_RELATED_ACTIVITIES' => 'Kopiera relaterade aktiviteter till',
    'LBL_CONVERT_MOVE_RELATED_ACTIVITIES' => 'Flytta relaterade aktiviteter till',
    'LBL_CONVERT_MOVE_ACTIVITIES_TO_CONTACT' => 'Flytta relaterade aktiviteter till kontaktposten',
    'LBL_CONVERTED_ACCOUNT'=>'Konverterad organisation:',
    'LBL_CONVERTED_CONTACT' => 'Konverterad kontakt:',
    'LBL_CONVERTED_OPP'=>'Konverterad affärsmöjlighet',
    'LBL_CONVERTED'=> 'Konverterad',
    'LBL_CONVERTLEAD_BUTTON_KEY' => 'V',
    'LBL_CONVERTLEAD_TITLE' => 'Konvertera lead [Alt+V]',
    'LBL_CONVERTLEAD' => 'Konvertera lead',
    'LBL_CONVERTLEAD_WARNING' => 'Varning: Statusen har redan den status du försöker ändra till. Kontakt och/eller Konto posterna kan redan ha skapats från ditt lead. Om du önskar fortsätta ändra statusen, klicka "Spara". För att gå tillbaka utan att uppdatera, klicka "Avbryt".',
    'LBL_CONVERTLEAD_WARNING_INTO_RECORD' => 'Möjlig kontakt:',
    'LBL_CONVERTLEAD_ERROR' => 'Det går inte att konvertera leadet',
    'LBL_CONVERTLEAD_FILE_WARN' => 'Du konverterade framgångsrikt leadet {{leadName}}, men det fanns ett problem att ladda upp Bilagor på en eller flera poster',
    'LBL_CONVERTLEAD_SUCCESS' => 'Du konverterade framgångsrikt leadet {{leadName}}',
    'LBL_COUNTRY' => 'Land:',
    'LBL_CREATED_NEW' => 'Skapade ny',
	'LBL_CREATED_ACCOUNT' => 'Skapade ny organisation',
    'LBL_CREATED_CALL' => 'Skapade nytt telefonsamtal',
    'LBL_CREATED_CONTACT' => 'Skapade ny kontakt',
    'LBL_CREATED_MEETING' => 'Skapade nytt möte',
    'LBL_CREATED_OPPORTUNITY' => 'Skapade ny affärsmöjlighet',
    'LBL_DEFAULT_SUBPANEL_TITLE' => 'Leads',
    'LBL_DEPARTMENT' => 'Avdelning:',
    'LBL_DESCRIPTION_INFORMATION' => 'Beskrivande information',
    'LBL_DESCRIPTION' => 'Beskrivning',
    'LBL_DO_NOT_CALL' => 'Ring inte:',
    'LBL_DUPLICATE' => 'Liknande leads',
    'LBL_EMAIL_ADDRESS' => 'Mailadress:',
    'LBL_EMAIL_OPT_OUT' => 'Önskar ej utskick:',
    'LBL_EXISTING_ACCOUNT' => 'Använde en existerande organisation',
    'LBL_EXISTING_CONTACT' => 'Använde en existerande kontakt',
    'LBL_EXISTING_OPPORTUNITY' => 'Använde en existerande affärsmöjlighet',
    'LBL_FAX_PHONE' => 'Fax:',
    'LBL_FIRST_NAME' => 'Förnamn:',
    'LBL_FULL_NAME' => 'Namn:',
    'LBL_HISTORY_SUBPANEL_TITLE'=>'Historik',
    'LBL_HOME_PHONE' => 'Hemtelefon:',
    'LBL_IMPORT_VCARD' => 'Importera vCard',
    'LBL_IMPORT_VCARD_SUCCESS' => 'Lead  från vCard skapades utan problem',
    'LBL_VCARD' => 'vCard',
    'LBL_IMPORT_VCARDTEXT' => 'Skapa ett lead automatiskt vid import av vCard från ditt filsystem.',
    'LBL_INVALID_EMAIL'=>'Ogiltig mailadress:',
    'LBL_INVITEE' => 'Direkt rapporter',
    'LBL_LAST_NAME' => 'Efternamn',
    'LBL_LEAD_SOURCE_DESCRIPTION' => 'Lead källa, beskrivning:',
    'LBL_LEAD_SOURCE' => 'Leadkälla:',
    'LBL_LIST_ACCEPT_STATUS' => 'Acceptera Status',
    'LBL_LIST_ACCOUNT_NAME' => 'Organisationsnamn',
    'LBL_LIST_CONTACT_NAME' => 'Lead namn',
    'LBL_LIST_CONTACT_ROLE' => 'Roll',
    'LBL_LIST_DATE_ENTERED' => 'Datum skapat',
    'LBL_LIST_EMAIL_ADDRESS' => 'Email',
    'LBL_LIST_FIRST_NAME' => 'Förnamn',
    'LBL_VIEW_FORM_TITLE' => 'Lead vy',
    'LBL_LIST_FORM_TITLE' => 'Lista leads',
    'LBL_LIST_LAST_NAME' => 'Efternamn',
    'LBL_LIST_LEAD_SOURCE_DESCRIPTION' => 'Lead källa, beskrivning',
    'LBL_LIST_LEAD_SOURCE' => 'Leadkälla',
    'LBL_LIST_MY_LEADS' => 'Mina leads',
    'LBL_LIST_NAME' => 'Namn',
    'LBL_LIST_PHONE' => 'Kontorstelefon',
    'LBL_LIST_REFERED_BY' => 'Refererad av',
    'LBL_LIST_STATUS' => 'Status',
    'LBL_LIST_TITLE' => 'Titel',
    'LBL_MOBILE_PHONE' => 'Mobil:',
    'LBL_MODULE_NAME' => 'Leads',
    'LBL_MODULE_NAME_SINGULAR' => 'Möjlig kund',
    'LBL_MODULE_TITLE' => 'Leads: Hem',
    'LBL_NAME' => 'Namn',
    'LBL_NEW_FORM_TITLE' => 'Nytt lead',
    'LBL_NEW_PORTAL_PASSWORD' => 'Nytt portallösenord:',
    'LBL_OFFICE_PHONE' => 'Kontorstelefon',
    'LBL_OPP_NAME' => 'Namn på affärsmöjligheten:',
    'LBL_OPPORTUNITY_AMOUNT' => 'Affärsmöjlighet summa',
    'LBL_OPPORTUNITY_ID'=>'Affärsmöjlighet ID',
    'LBL_OPPORTUNITY_NAME' => 'Namn på affärsmöjligheten:',
    'LBL_CONVERTED_OPPORTUNITY_NAME' => 'Namn på konverterad affärsmöjlighet',
    'LBL_OTHER_EMAIL_ADDRESS' => 'Annan email:',
    'LBL_OTHER_PHONE' => 'Annan telefon:',
    'LBL_PHONE' => 'Telefon:',
    'LBL_PORTAL_ACTIVE' => 'Portal aktiv:',
    'LBL_PORTAL_APP'=> 'Portal applikation:',
    'LBL_PORTAL_INFORMATION' => 'Portal information',
    'LBL_PORTAL_NAME' => 'Portalnamn:',
    'LBL_PORTAL_PASSWORD_ISSET' => 'Portallösenord är satt:',
    'LBL_POSTAL_CODE' => 'Postnummer:',
    'LBL_STREET' => 'Gata',
    'LBL_PRIMARY_ADDRESS_CITY' => 'Primär adress stad:',
    'LBL_PRIMARY_ADDRESS_COUNTRY' => 'Primär adress land:',
    'LBL_PRIMARY_ADDRESS_POSTALCODE' => 'Primär adress postnummer:',
    'LBL_PRIMARY_ADDRESS_STATE' => 'Primär stat/län',
    'LBL_PRIMARY_ADDRESS_STREET_2'=>'Primär gatuadress 2',
    'LBL_PRIMARY_ADDRESS_STREET_3'=>'Primär gatuadress 3',
    'LBL_PRIMARY_ADDRESS_STREET' => 'Primär gatuadress',
    'LBL_PRIMARY_ADDRESS' => 'Primär adress:',
    'LBL_RECORD_SAVED_SUCCESS' => 'Du har skapat med framgång {{moduleSingularLower}} <a href="#{{buildRoute model=this}}">{{full_name}}</a>.',
    'LBL_REFERED_BY' => 'Refererad av:',
    'LBL_REPORTS_TO_ID'=>'Rapporterar till ID:',
    'LBL_REPORTS_TO' => 'Rapporterar till:',
    'LBL_REPORTS_FROM' => 'Rapporter Från:',
    'LBL_SALUTATION' => 'Titel:',
    'LBL_MODIFIED'=>'Uppdaterad av:',
	'LBL_MODIFIED_ID'=>'Uppdaterad av Id',
	'LBL_CREATED'=>'Skapad av',
	'LBL_CREATED_ID'=>'Skapad av Id',
    'LBL_SEARCH_FORM_TITLE' => 'Sök lead',
    'LBL_SELECT_CHECKED_BUTTON_LABEL' => 'Välj markerade leads',
    'LBL_SELECT_CHECKED_BUTTON_TITLE' => 'Välj markerade leads',
    'LBL_STATE' => 'Stat:',
    'LBL_STATUS_DESCRIPTION' => 'Status beskrivning:',
    'LBL_STATUS' => 'Status:',
    'LBL_TITLE' => 'Titel:',
    'LBL_UNCONVERTED'=> 'Okonverterad',
    'LNK_IMPORT_VCARD' => 'Skapa från vCard',
    'LNK_LEAD_LIST' => 'Leads',
    'LNK_NEW_ACCOUNT' => 'Skapa organisation',
    'LNK_NEW_APPOINTMENT' => 'Skapa händelse',
    'LNK_NEW_CONTACT' => 'Skapa kontakt',
    'LNK_NEW_LEAD' => 'Skapa lead',
    'LNK_NEW_NOTE' => 'Skapa anteckning eller bilaga',
    'LNK_NEW_TASK' => 'Skapa uppgift',
    'LNK_NEW_CASE' => 'Skapa ärende',
    'LNK_NEW_CALL' => 'Logga samtal',
    'LNK_NEW_MEETING' => 'Schemalägg möte',
    'LNK_NEW_OPPORTUNITY' => 'Skapa affärsmöjlighet',
	'LNK_SELECT_ACCOUNTS' => '<B>ELLER</B> Välj konto',
    'LNK_SELECT_CONTACTS' => 'ELLER Välj Kontakt',
    'NTC_COPY_ALTERNATE_ADDRESS' => 'Kopiera alternativ adress till primär adress',
    'NTC_COPY_PRIMARY_ADDRESS' => 'Kopiera primär adress till alternativ adress',
    'NTC_DELETE_CONFIRMATION' => 'Är du säker på att du vill radera posten?',
    'NTC_OPPORTUNITY_REQUIRES_ACCOUNT' => 'När en affärsmöjlighet skapas krävs en organisation.\n Var god skapa en ny organisation eller välj en existerande.',
    'NTC_REMOVE_CONFIRMATION' => 'Är du säker på att du vill ta bort leadet från ärendet?',
    'NTC_REMOVE_DIRECT_REPORT_CONFIRMATION' => 'Är du säker på att du vill ta bort det här objektet som en direkt rapport?',
    'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE'=>'Kampanjer',
    'LBL_TARGET_OF_CAMPAIGNS'=>'Lyckad kampanj:',
    'LBL_TARGET_BUTTON_LABEL'=>'Mål',
    'LBL_TARGET_BUTTON_TITLE'=>'Mål',
    'LBL_TARGET_BUTTON_KEY'=>'T',
    'LBL_CAMPAIGN' => 'Kampanj:',
  	'LBL_LIST_ASSIGNED_TO_NAME' => 'Tilldelad användare',
    'LBL_PROSPECT_LIST' => 'Prospektlista',
    'LBL_PROSPECT' => 'Mål',
    'LBL_CAMPAIGN_LEAD' => 'Kampanjer',
	'LNK_LEAD_REPORTS' => 'Lead rapporter',
    'LBL_BIRTHDATE' => 'Födelsedag:',
    'LBL_THANKS_FOR_SUBMITTING_LEAD' =>'Tack för din prenumeration.',
    'LBL_SERVER_IS_CURRENTLY_UNAVAILABLE' =>'Vi beklagar, servern är tyvärr ej nåbar för tillfället, var god försök igen senare.',
    'LBL_ASSISTANT_PHONE' => 'Assistents telefon',
    'LBL_ASSISTANT' => 'Assistent',
    'LBL_REGISTRATION' => 'Registrering',
    'LBL_MESSAGE' => 'Vänligen fyll i din information här under. Information och/eller en organisation kommer att skapas för din<br /> Information and/or an account will be created for you ansökan.',
    'LBL_SAVED' => 'Tack för din registrering. Din organisation kommer att skapas och någon kommer kontakta dig inom kort.',
    'LBL_CLICK_TO_RETURN' => 'Tillbaka till Portal',
    'LBL_CREATED_USER' => 'Skapad användare',
    'LBL_MODIFIED_USER' => 'Ändrad användare',
    'LBL_CAMPAIGNS' => 'Kampanjer',
    'LBL_CAMPAIGNS_SUBPANEL_TITLE' => 'Kampanjer',
    'LBL_CONVERT_MODULE_NAME' => 'Modul',
    'LBL_CONVERT_MODULE_NAME_SINGULAR' => 'Modul',
    'LBL_CONVERT_REQUIRED' => 'Obligatorisk',
    'LBL_CONVERT_SELECT' => 'Tillåt val',
    'LBL_CONVERT_COPY' => 'Kopiera data',
    'LBL_CONVERT_EDIT' => 'Redigera',
    'LBL_CONVERT_DELETE' => 'Radera',
    'LBL_CONVERT_ADD_MODULE' => 'Lägg till modul',
    'LBL_CONVERT_EDIT_LAYOUT' => 'Ändra konverterings layout',
    'LBL_CREATE' => 'Skapa',
    'LBL_SELECT' => '<b>OR</B> Välj',
	'LBL_WEBSITE' => 'Hemsida',
	'LNK_IMPORT_LEADS' => 'Importera leads',
	'LBL_NOTICE_OLD_LEAD_CONVERT_OVERRIDE' => 'Notis: Den nuvarande konvertera leads vyn innehåller specialfält. När du skräddarsyr konvertera leads vyn i Studio första gången så måste du addera specialfält till layouten. Specialfält kommer inte att dyka upp automatiskt i layouten som de gjorde tidigare.',
//Convert lead tooltips
	'LBL_MODULE_TIP' 	=> 'Modulen att skapa nya poster i.',
	'LBL_REQUIRED_TIP' 	=> 'Obligatorisk modul måste först skapas eller väljas innan ett lead kan konverteras.',
	'LBL_COPY_TIP'		=> 'Om checkad, fält från det lead som ska kopieras kommer att kopieras till fält med samma namn i nyligen skapade poster.',
	'LBL_SELECTION_TIP' => 'Moduler med ett relationsfält i kontakter kan väljas istället för skapas under lead konverteringsprocessen.',
	'LBL_EDIT_TIP'		=> 'Ändra konverterings layouten för denna modul.',
	'LBL_DELETE_TIP'	=> 'Ta bort denna modul från konverterings layouten.',

    'LBL_ACTIVITIES_MOVE'   => 'Fler Aktiviteter till',
    'LBL_ACTIVITIES_COPY'   => 'Kopiera Aktiviteter till',
    'LBL_ACTIVITIES_MOVE_HELP'   => "Välj vilken post att flytta Leadens aktiviteter till. Arbetsuppgifter, Samtal, Möten, Anteckningar och mail kommer flyttas till de(n) valda posten(rna).",
    'LBL_ACTIVITIES_COPY_HELP'   => "Välj vilken post att kopiera Leadens aktiviteter till. Nya arbetsuppgifter, Samtal, Möten och Anteckningar kommer kopieras till de(n) valda posten(rna). Email kommer att relateras till valda post(er).",
    //For export labels
    'LBL_PHONE_HOME' => 'Telefon Hem',
    'LBL_PHONE_MOBILE' => 'Telefon Mobil',
    'LBL_PHONE_WORK' => 'Telefon Jobb',
    'LBL_PHONE_OTHER' => 'Telefon Övrig',
    'LBL_PHONE_FAX' => 'Telefon fax',
    'LBL_CAMPAIGN_ID' => 'Kampanj Id',
    'LBL_EXPORT_ASSIGNED_USER_NAME' => 'Tilldelat Användarnamn',
    'LBL_EXPORT_ASSIGNED_USER_ID' => 'Tilldelad Användar ID',
    'LBL_EXPORT_MODIFIED_USER_ID' => 'Ändrad av ID',
    'LBL_EXPORT_CREATED_BY' => 'Skapad av ID',
    'LBL_EXPORT_PHONE_MOBILE' => 'Mobiltelefon',
    'LBL_EXPORT_EMAIL2'=>'Annan mailadress',
	'LBL_EDITLAYOUT' => 'Redigera layout' /*for 508 compliance fix*/,
	'LBL_ENTERDATE' => 'Fyll i datum' /*for 508 compliance fix*/,
	'LBL_LOADING' => 'Laddar' /*for 508 compliance fix*/,
	'LBL_EDIT_INLINE' => 'Redigera' /*for 508 compliance fix*/,
    //D&B Principal Identification
    'LBL_DNB_PRINCIPAL_ID' => 'D&B Huvud-ID',
    //Dashlet
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'Affärsmöjligheter',

    //Document title
    'TPL_BROWSER_SUGAR7_RECORDS_TITLE' => '{{module}} &raquo; {{appId}}',
    'TPL_BROWSER_SUGAR7_RECORD_TITLE' => '{{#if last_name}}{{#if first_name}}{{first_name}} {{/if}}{{last_name}} &raquo; {{/if}}{{module}} &raquo; {{appId}}',
    'LBL_NOTES_SUBPANEL_TITLE' => 'Anteckningar',

    'LBL_HELP_CONVERT_TITLE' => 'Konvertera en {{module_name}}',

    // Help Text
    // List View Help Text
    'LBL_HELP_RECORDS' => '{{plural_module_name}}modulen består av individuella förutsättningar som kan vara intresserade av en produkt eller tjänst ditt företag tillhandahåller. När ett {{modul}} är kvalificerad som en försäljning {{opportunities_singular_module}}, {{plural_module_name}} kan den omvandlas till {{contacts_module}}, {{opportunities_module}} och {{accounts_module}}. Det finns olika sätt som du kan skapa {{plural_module_name}} i Sugar exempel via {{plural_module_name}} modul, duplicara, importera {{plural_module_name}}, etc. När {{modul}} post skapas, kan du visa och redigera information som hänför sig till {{modul}} via {{plural_module_name}}vyn.',

    // Record View Help Text
    'LBL_HELP_RECORD' => '{{plural_module_name}}modulen består av individuella förutsättningar som kan vara intresserade av en produkt eller tjänst ditt företag tillhandahåller.  Redigera denna postens fält genom att klicka ett enskilt fält eller på knappen Redigera. - Visa eller ändra länkar till andra poster i underpaneler, även {{campaigns_singular_module}} mottagare, genom att växla den nedre vänstra rutan till "Data View". 
- Utför och se användarkommentarer och eller se förändringar i {{activitystream_singular_module}} genom att växla den nedre vänstra rutan på "Activity Stream". - Följ som favorit med hjälp av ikonerna till höger om namnet. - Ytterligare åtgärder finns i dropdown menyn Åtgärder till höger om knappen Redigera.',

    // Create View Help Text
    'LBL_HELP_CREATE' => '{{plural_module_name}}modulen består av individuella potentiella köpare som kan vara intresserade av en produkt eller tjänst ditt företag tillhandahåller. När {{module_name}}en har kvalificerats som en försäljnings{{opportunities_singular_module}} kan den konverteras till en {{contacts_singular_module}}, {{accounts_singular_module}}, {{opportunities_singular_module}}, eller annan post.

För att skapa en {{module_name}}: 
1. Lägg in de värden i de fält som önskas. 
- Fält markerade "Obligatorisk" måste fyllas i innan du sparar. 
- Klicka på "Visa mer" för att visa ytterligare fält. 
2. Klicka på "Spara" för att spara den nya posten och återgå till föregående sida.',

    // Convert View Help Text
    'LBL_HELP_CONVERT' => 'Sugar kan du konvertera {{plural_module_name}} till {{contacts_module}}, {{accounts_module}} och andra moduler när {{modul}} uppfyller dina kompetenskrav. Stega genom varje modul genom att ändra fälten därefter bekräfta de nya värdena genom att klicka på respektive assicieraknappen. Om Sugar upptäcker en befintlig post som matchar din {{modul}}s uppgifter, har du möjlighet att välja en dubblett och bekräfta valet med assicieraknappen eller klicka på "Ignorera och skapa nya" och fortsätt som vanligt. Efter att ha bekräftat varje val och önskad modul klickar du på Spara och konvertera knappen högst upp för att slutföra omvandlingen.',

    //Marketo
    'LBL_MKTO_SYNC' => 'Synka till Market&reg;',
    'LBL_MKTO_ID' => 'Marketo Lead ID',
    'LBL_MKTO_LEAD_SCORE' => 'Lead score',

    'LBL_FILTER_LEADS_REPORTS' => 'Leads&#39; rapporter',
    'LBL_DATAPRIVACY_BUSINESS_PURPOSE' => 'Affärsändamål som samtyckts till',
    'LBL_DATAPRIVACY_CONSENT_LAST_UPDATED' => 'Samtycke uppdaterades senast',

    // Leads Pipeline view
    'LBL_PIPELINE_ERR_CONVERTED' => 'Kunde inte ändra status för {{moduleSingular}}. Denna {{moduleSingular}} har redan konverterats.',
);
