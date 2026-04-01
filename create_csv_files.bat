@echo off
mkdir csv 2>nul
echo CSV folder created or already exists

echo Creating bank_transactions.csv...
(
echo date,description,amount,external_id
echo 2026-03-15,Virement client SARL Dupont,2500.00,TRX-2026-001
echo 2026-03-16,Prélèvement loyer bureaux,-850.00,TRX-2026-002
echo 2026-03-17,Paiement fournisseur Tech Solutions,-1200.50,TRX-2026-003
echo 2026-03-18,Virement client Entreprise Martin,3750.00,TRX-2026-004
echo 2026-03-19,Prélèvement assurance,-120.00,TRX-2026-005
echo 2026-03-20,Virement salaires,-4500.00,TRX-2026-006
echo 2026-03-21,Paiement CB Restaurant Le Gourmet,-85.50,TRX-2026-007
echo 2026-03-22,Virement client SAS Bernard,1890.00,TRX-2026-008
echo 2026-03-23,Prélèvement électricité EDF,-180.30,TRX-2026-009
echo 2026-03-24,Paiement fournitures Bureau Plus,-245.80,TRX-2026-010
echo 2026-03-25,Virement client EURL Thomas,4200.00,TRX-2026-011
echo 2026-03-26,Prélèvement Internet/Téléphone,-89.99,TRX-2026-012
echo 2026-03-27,Paiement CB Carburant Total,-95.00,TRX-2026-013
echo 2026-03-28,Virement client SA Mercier,2100.00,TRX-2026-014
echo 2026-03-29,Prélèvement comptable expert,-350.00,TRX-2026-015
echo 2026-03-30,Virement remboursement TVA,1250.00,TRX-2026-016
echo 2026-03-31,Paiement logiciel SaaS Microsoft,-45.00,TRX-2026-017
) > csv\bank_transactions.csv

echo Creating invoices_import.csv...
(
echo customer_name,invoice_number,issue_date,due_date,description,quantity,unit_price,vat_rate,status
echo SARL Dupont,FAC-2026-001,2026-03-01,2026-03-31,Prestation de conseil mensuel,1,2500.00,20,sent
echo Entreprise Martin,FAC-2026-002,2026-03-05,2026-04-04,Développement application web,1,3750.00,20,sent
echo SAS Bernard,FAC-2026-003,2026-03-10,2026-04-09,Formation équipe (2 jours^),2,945.00,20,sent
echo EURL Thomas,FAC-2026-004,2026-03-15,2026-04-14,Maintenance serveurs (Q1 2026^),1,4200.00,20,sent
echo SA Mercier,FAC-2026-005,2026-03-20,2026-04-19,Support technique mensuel,1,2100.00,20,sent
echo SARL Petit,FAC-2026-006,2026-03-25,2026-04-24,Audit sécurité informatique,1,1800.00,20,draft
echo Entreprise Rousseau,FAC-2026-007,2026-03-28,2026-04-27,Hébergement cloud mensuel,1,450.00,20,draft
) > csv\invoices_import.csv

echo Creating expenses_import.csv...
(
echo date,category,supplier,amount,vat_amount,description
echo 2026-03-21,meals,Restaurant Le Gourmet,71.25,14.25,Déjeuner client prospect
echo 2026-03-24,supplies,Bureau Plus,204.83,40.97,Fournitures bureau
echo 2026-03-27,travel,Station Total,79.17,15.83,Carburant déplacement client
echo 2026-03-15,travel,SNCF,156.00,31.20,Train Paris-Lyon réunion client
echo 2026-03-18,meals,Brasserie du Commerce,45.00,9.00,Déjeuner équipe
echo 2026-03-22,supplies,Amazon Business,98.33,19.67,Matériel informatique
echo 2026-03-26,utilities,Orange,75.00,15.00,Forfait mobile entreprise
echo 2026-03-29,other,Librairie Technique,65.00,13.00,Livres formation
echo 2026-03-16,travel,Europcar,280.00,56.00,Location véhicule 2 jours
echo 2026-03-23,meals,Café Central,28.33,5.67,Petit-déjeuner réunion
) > csv\expenses_import.csv

echo Creating customers_import.csv...
(
echo name,email,phone,address,city,postal_code,country,siret,vat_number
echo SARL Dupont,contact@dupont-sarl.fr,0145678901,12 Rue de la Paix,Paris,75001,France,12345678900012,FR12345678901
echo Entreprise Martin,info@martin-entreprise.fr,0234567890,45 Avenue des Champs,Lyon,69001,France,23456789000023,FR23456789012
echo SAS Bernard,direction@bernard-sas.fr,0345678901,78 Boulevard Victor Hugo,Marseille,13001,France,34567890000034,FR34567890123
echo EURL Thomas,contact@thomas-eurl.fr,0456789012,23 Rue Nationale,Lille,59000,France,45678901000045,FR45678901234
echo SA Mercier,info@mercier-sa.fr,0567890123,91 Avenue de la République,Toulouse,31000,France,56789012000056,FR56789012345
echo SARL Petit,contact@petit-sarl.fr,0678901234,34 Rue du Commerce,Bordeaux,33000,France,67890123000067,FR67890123456
echo Entreprise Rousseau,direction@rousseau-ent.fr,0789012345,67 Place Bellecour,Lyon,69002,France,78901234000078,FR78901234567
) > csv\customers_import.csv

echo.
echo ========================================
echo CSV files created successfully!
echo ========================================
echo.
echo Location: c:\Users\Zafir\Herd\comptafinance\csv\
echo.
echo Files created:
echo   - bank_transactions.csv (17 transactions)
echo   - invoices_import.csv (7 invoices)
echo   - expenses_import.csv (10 expenses)
echo   - customers_import.csv (7 customers)
echo.
echo You can now test the import functionality!
echo.
pause
