<div class="oleez-loader"></div>
<header class="oleez-header">
  <nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="index.html"><img src="<?=base_url()?>/assets_frontend/assets/images/Logo_2.svg" alt="Oleez"></a>
    <ul class="nav nav-actions d-lg-none ml-auto">
      <li class="nav-item dropdown d-none d-sm-block">
        <a class="nav-link dropdown-toggle" href="#!" id="languageDropdown" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">ENG</a>
        <div class="dropdown-menu" aria-labelledby="languageDropdown">
          <a class="dropdown-item" href="#!">ARB</a>
          <a class="dropdown-item" href="#!">FRE</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#!" data-toggle="offCanvasMenu">
          <img src="<?=base_url()?>/assets_frontend/assets/images/social icon@2x.svg" alt="social-nav-toggle">
        </a>
      </li>
    </ul>
    <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#oleezMainNav" aria-controls="oleezMainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="oleezMainNav">
      <ul class="navbar-nav mx-auto mt-2 mt-lg-0">
        <li class="nav-item active">
          <a class="nav-link" href="<?=base_url()?>">Home <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?=base_url()?>/about_us">Apropos de nous</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" data-toggle="dropdown"
          aria-haspopup="true" aria-expanded="false">Salle</a>
          <div class="dropdown-menu" aria-labelledby="pagesDropdown">
            <a class="dropdown-item" href="<?=base_url()?>/mariage">Mariage</a>
            <a class="dropdown-item" href="<?=base_url()?>/conferences">Conférences</a>
            <a class="dropdown-item" href="<?=base_url()?>/autres_evenements">Autres évènements</a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?=base_url()?>/contact_nous">Contact nous</a>
        </li>
      </ul>
    </div>
  </nav>
</header>