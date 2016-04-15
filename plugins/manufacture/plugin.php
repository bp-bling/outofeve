<?php

class manufacture extends Plugin {

    var $name = 'Industry';
    var $level = 1;

    function manufacture($db, $site) {
        $this->Plugin($db, $site);

        if (eveKeyManager::getKey($this->site->user->char_apikey_id)
                && eveKeyManager::getKey($this->site->user->char_apikey_id)->hasAccess(CHAR_IndustryJobs)) {
            $this->site->plugins['mainmenu']->addLink('main', 'Industry', '?module=manufacture', 'industry');
        }

        if (eveKeyManager::getKey($this->site->user->corp_apikey_id)
                && eveKeyManager::getKey($this->site->user->corp_apikey_id)->hasAccess(CORP_IndustryJobs)) {
            $this->site->plugins['mainmenu']->addLink('corp', 'Industry', '?module=manufacture&corp=1', 'industry');
        }
    }

    function getContent() {
        if (!isset($_GET['p'])) {
            $_GET['p'] = 0;
        }
        if (!isset($_GET['activity'])) {
            $_GET['activity'] = 0;
        }
        if (!isset($_GET['complete'])) {
            $_GET['complete'] = 0;
        }

        if (isset($_GET['corp'])) {
            if (eveKeyManager::getKey($this->site->user->corp_apikey_id) != null) {
                $il = new eveIndustryJobList(eveKeyManager::getKey($this->site->user->corp_apikey_id));
                $il->load();
            }
        } else {
            if (eveKeyManager::getKey($this->site->user->char_apikey_id) != null) {
                $il = new eveIndustryJobList(eveKeyManager::getKey($this->site->user->char_apikey_id));
                $il->load();
            }
        }
        $industryList = $il->industryJobs;

        $jobs = array();
        $activities = array();

        foreach ($industryList as $job) {
            if (!isset($activities[$job->activityID])) {
                $activities[$job->activityID] = $job->activity->activityname;
            }

            switch ($job->activityID) {
                case 1:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/manufacturing.png';
                    break;
                case 3:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/researchTime.png';
                    break;
                case 4:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/researchMaterial.png';
                    break;
                case 5:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/copying.png';
                    break;
                case 8:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/invention.png';
                    break;
                default:
                    $job->altIcon = 'eveimages/Icons/UI/Industry/manufacturing.png';
            }

            if (($_GET['activity'] == 0) || ($_GET['activity'] == $job->activityID)) {
                if (($_GET['complete'] > 0) || (($_GET['complete'] == 0) && ($job->completed == 0))) {
                    $jobs[] = objectToArray($job, array('DBManager', 'eveDB'));
                }
            }
        }
        asort($activities);

        $p = new Paginator($jobs, 50, $_GET['p']);

        return $this->render('jobs', array('jobs' => $p->pageData, 'activities' => $activities, 'activity' => $_GET['activity'], 'complete' => $_GET['complete'],
                    'pageCount' => $p->pageCount, 'pageNum' => $p->pageNum, 'nextPage' => $p->nextPage, 'prevPage' => $p->prevPage, 'corp' => isset($_GET['corp'])));
    }

}

?>
