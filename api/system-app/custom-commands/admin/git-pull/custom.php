<?php

/**
 * Настройка GIT
 */
shell_exec( "git config user.email 'vladislav.tsyrdea@gmail.com'" );
shell_exec( "git config user.name 'Vladislav_TS'" );
shell_exec( "git config remote.origin.url 'https://Vladislav_TS:TKxQApTTFHubN6crU5Vf@bitbucket.org/Vladislav_TS/api-core-v3.git'" );
shell_exec( "git config credential.helper store" );

/**
 * Обновление репозитория
 */
shell_exec( "git fetch" );
shell_exec( "git pull" );