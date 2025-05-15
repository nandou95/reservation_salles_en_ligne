  <?php
    if ($this->session->userdata('USER_ID')==NULL) {


      redirect(base_url('Login'));
      // code...
    }
    $menu = $this->uri->segment(1);
    $sousmenu = $this->uri->segment(2);
   ?>

  <nav id="sidebar" class="sidebar">
            <a class="sidebar-brand text-center" href="index.html">
                <img width="150px" src="<?= base_url() ?>template/img/paeej_logo.jpg">
            </a>
            <div class="sidebar-content">

                <div class="sidebar-user">
                    <div class="font-weight-bold"><?= $this->session->userdata('POSTE') ?></div>
                    <small><?= $this->session->userdata('USER_NAME') ?></small>
                    <hr>
                </div>

                <ul class="sidebar-nav">

                     <li class="sidebar-item <?php if($menu == 'apply') echo 'active' ?>">
                        <a href="<?= base_url() ?>apply/Demandes/views" class="sidebar-link collapsed">
                           <i class="align-middle mr-2 fa fa-tasks"></i> <span class="align-middle">Application</span>
                        </a>
                        
                    </li>
                    
               

                       <li class="sidebar-item <?php if($menu == 'dashboard') echo 'active' ?>">
                        <a href="#dash" data-toggle="collapse" class="sidebar-link collapsed">
                            <i class="fa fa-pie-chart"></i> <span class="align-middle">Tableau de bord</span>
                        </a>
                        <ul id="dash" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
                              
                         <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Ben_Benaficiaire') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Ben_Benaficiaire">Bénéficiaires</a></li>
                         
                         <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Ben_Beneficiaire_Porcs') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Ben_Beneficiaire_Porcs">Suivi/Porcs</a></li>

                         <!-- <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Ben_Beneficiaire_Porcs') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Ben_Beneficiaire_Porcs">Suivi/Porcs</a></li> -->

                        <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Beneficiaire_Poules_Chairs') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Beneficiaire_Poules_Chairs">Suivi/Poules de chair</a></li>

                            <!-- <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Demande_Formation') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Demande_Formation">Formations</a></li> -->

                             <!-- <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Demande_Stage') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Demande_Stage">Stages</a></li> -->

                          
                          <!--  <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Paeej_Projet') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Paeej_Projet">Projet</a></li> -->

                            <!-- <li class="sidebar-item <?php if($sousmenu == 'Dashboard_Declaration_Plainte') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>dashboard/Dashboard_Declaration_Plainte">Plaintes</a></li> -->

                        </ul>



                    </li>

                    <li class="sidebar-item <?php if($menu == 'map') echo 'active' ?>">
                        <a href="#sig" data-toggle="collapse" class="sidebar-link collapsed">
                           <i class="align-middle mr-2 fa fa-map"></i> <span class="align-middle">SIG</span>
                        </a>
                        <ul id="sig" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
                             <li class="sidebar-item <?php if($sousmenu == 'Rhmap') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>map/Rhmap">Carte des acteurs</a></li>
                            <li class="sidebar-item <?php if($sousmenu == 'Projetmap') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>map/Projetmap">Carte des Projets et Formations</a></li>
                        </ul>
                    </li>


<!-- 
                     <li class="sidebar-item <?php if($menu == 'demandes') echo 'active' ?>">
                        <a href="#demandes" data-toggle="collapse" class="sidebar-link collapsed">
                           <i class="align-middle mr-2 fa fa-window-restore"></i> <span class="align-middle">Demandes</span>
                        </a>
                        <ul id="demandes" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
                             <li class="sidebar-item <?php if($sousmenu == 'Process_Demande_Financement_Entrepreneuriat') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>demande/Process_Demande_Financement_Entrepreneuriat">Liste des demandes de financement (Guichet entrepreneuriat)</a>
                             </li>

                             <li class="sidebar-item <?php if($sousmenu == 'Demande_formation_entrepreneuriat') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>demande/Demande_formation_entrepreneuriat">Liste des formations (Guichet entrepreneuriat)</a>
                             </li>

                             <li class="sidebar-item <?php if($sousmenu == 'Process_Financement_entrepreneuriat') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>demande/Process_Financement_entrepreneuriat">Liste des financements (Guichet entrepreneuriat)</a>
                             </li>

                             <li class="sidebar-item <?php if($sousmenu == 'Process_Accompagnement_entrepreneuriat') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>demande/Process_Accompagnement_entrepreneuriat">Liste accompagnement (Guichet entrepreneuriat)</a>
                             </li>

                             <li class="sidebar-item <?php if($sousmenu == 'Etape_process') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>demande/Etape_process">Configuration des piliers</a>
                             </li>
                        </ul>
                    </li> -->


                    <li class="sidebar-item <?php if($menu == 'ihm' || $menu == 'insertion_prof') echo 'active' ?>">
                        <a href="#ihm" data-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle mr-2 fa fa-address-book"></i> <span class="align-middle">IHM</span>
                        </a>
                        <ul id="ihm" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">

                      <li class="sidebar-item <?php if($sousmenu == 'Collaborateurs') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>ihm/Collaborateurs">Collaborateurs</a>
                            </li>

                        <li class="sidebar-item"><a class="sidebar-link" href="<?= base_url() ?>ihm/Type_Partenaire">Type de partenaire</a></li>
                         <li class="sidebar-item <?php if($sousmenu == 'Partenaire') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>ihm/Partenaire">Partenaire</a>
                         </li>

                         <li class="sidebar-item <?php if($sousmenu == 'Beneficiaire') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>beneficiaire/Beneficiaire">Bénéficiaire</a>
                         </li>
                         <!--<li class="sidebar-item <?php if($sousmenu == 'Resultat') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>ihm/Resultat">Résultat</a>
                         </li> -->

                         <li class="sidebar-item <?php if($sousmenu == 'Enregistrement_Dossier') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>insertion_prof/Enregistrement_Dossier">Dossier</a>
                         </li>

                         <li class="sidebar-item <?php if($sousmenu == 'Selection_Dossier') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>insertion_prof/Selection_Dossier/index">Sélection des dossiers</a>
                         </li>

                         <li class="sidebar-item <?php if($sousmenu == 'Accompagnement_stagiaires') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>insertion_prof/Accompagnement_stagiaires">Accompagnement des stagiaires</a>
                         </li>
 
                        </ul>
                    </li>



                    <li class="sidebar-item <?php if($menu == 'projet' || $menu == 'formation' || $menu == 'cra') echo 'active' ?>">
                        <a href="#proj" data-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle mr-2 fa fa-cubes"></i> <span class="align-middle">Projet</span>
                        </a>
                        <ul id="proj" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
                          <li class="sidebar-item <?php if($sousmenu == 'Projets') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>cra/Projets/liste">Projet</a></li>
                          <li class="sidebar-item <?php if($sousmenu == 'Decaissement') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>cra/Decaissement/index">Décaissement</a></li>
                          <!-- <li class="sidebar-item <?php if($sousmenu == 'Formation') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>formation/Formation">Formation</a></li> -->

                          <li class="sidebar-item <?php if($sousmenu == 'Taches') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>cra/Taches">Taches</a></li>

                          <li class="sidebar-item <?php if($sousmenu == 'Activite') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>cra/Activite">Activités</a></li>

                          <li class="sidebar-item <?php if($sousmenu == 'cra_cra') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>/cra/cra_cra">Affectations</a></li>

                          <!-- <li class="sidebar-item <?php if($sousmenu == 'Taches') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>cra/Taches">CRA</a></li> -->


                        </ul>
                    </li>


                      <li class="sidebar-item <?php if($menu == 'administration' || $menu == 'configuration' || $menu == 'ihm') echo 'active' ?>">
                        <a href="#admin" data-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle mr-2 fa fa-cogs"></i> <span class="align-middle">Administration</span>
                        </a>
                        <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">

                            <li class="sidebar-item <?php if($sousmenu == 'Departement') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>administration/Departement">Départements</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Proc_Services') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Proc_Services">Services</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Profil_User') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Pro">Profils</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Users') echo 'active' ?> "><a class="sidebar-link" href="<?= base_url() ?>administration/Users">Utilisateurs</a></li>

                           

                            <li class="sidebar-item <?php if($sousmenu == 'Piliers') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Piliers">Piliers</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Processus') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Processus">Processus</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Etape') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Etape">Etape</a></li>

                            <li class="sidebar-item <?php if($sousmenu == 'Action') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Action">Actions</a></li>

                           <li class="sidebar-item <?php if($sousmenu == 'Process_document') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Process_document">Type de documents</a></li>


                       <!--  <li class="sidebar-item <?php if($sousmenu == 'Collaborateurs') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Collaborateurs">Collaborateurs</a></li> -->

                    <li class="sidebar-item <?php if($sousmenu == 'Process_Formation') echo 'active' ?>"><a class="sidebar-link" href="<?= base_url() ?>configuration/Process_Formation">Process Formation</a></li>

                        </ul>
                    </li>

                </ul>
            </div>
        </nav>
