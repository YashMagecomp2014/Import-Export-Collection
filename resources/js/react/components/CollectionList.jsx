import React, { useEffect, useState, useCallback } from "react"
import CollectionPage from "./CollectionPage"
import Dropdown from "./Dropdown"
import GetAllcollection from "./GetAllcollection";
import HistoryList from './HistoryList';
import PlanComponent from './PlanComponent';
import GetAutocollection from './GetAutocollection';
import GetManualcollection from './GetManualcollection';
import { Link } from "react-router-dom";
import { GlobalAPIcall } from "../config/ApiUtils"
import { Card, Tabs } from '@shopify/polaris';
import { useAppBridge } from "@shopify/app-bridge-react";
import { Fullscreen } from "@shopify/app-bridge/actions";
import { useDispatch, useSelector } from "react-redux";
import { setRedirectIndex } from "../redux/rootReducer";
import { Toast, Frame } from '@shopify/polaris';
import { Button, Popover, ActionList } from '@shopify/polaris';

function CollectionList({ currentPlan }) {//"NO_PLAN"|"FREE"|"PLAN1"|"PLAN2"
  const redirectIndex = useSelector((state) => state.redirectHistory);
  const [collections, setUsers] = useState([]);
  const [selected, setSelected] = useState(0);
  const [toastactive, setToastActive] = useState(false);
  const [inputfield, setInputField] = useState(true);
  const [option, setOption] = useState('all');
  const [active, setActive] = useState(false);

  console.log(currentPlan, "CollectionList1")
  const toggleActive = useCallback(() => setActive((active) => !active), []);
  const dispatch = useDispatch();

  const fetchData = async () => {
    var res = await GlobalAPIcall('GET', '/import');
    setUsers(res)

  }

  const handleAllAction = (key) => {
    setOption(key);
  }

  const activator = (
    <a onClick={toggleActive}>
      Collections
    </a>
  );
  const handleTabChange = useCallback((selectedTabIndex) => {
    

    if (currentPlan == "NO_PLAN") {
      setSelected(2);
      // alert(2);
    } else {
      setSelected(selectedTabIndex);
    }
  }, []);

  const app = useAppBridge();
  const fullscreen = Fullscreen.create(app);
  // Call the `ENTER` action to put the app in full-screen mode
  useEffect(() => {
    console.log(currentPlan, "CollectionList2")
    if (currentPlan == "NO_PLAN") {
      setSelected(2);
    } else {
      if (redirectIndex) {
        setSelected(1);
        dispatch(setRedirectIndex(false));
      }
    }
  }, [redirectIndex]);
  const setselectvalue = () => {
    setSelected(1);
  }


  const component = [
    <GetAllcollection setselectvalue={setselectvalue} options={option} />,
    <HistoryList />,
    <PlanComponent />
  ];

  useEffect(() => {
    component;
  }, [option])

  const tabs = [
    {
      id: 'all-customers-1',
      content:
        <div>
          <Popover
            active={active}
            activator={activator}
            autofocusTarget="first-node"
            onClose={toggleActive}
          >
            <ActionList
              actionRole="menuitem"
              items={[
                {
                  content: 'All',
                  onAction: () => handleAllAction('all'),
                  disabled: currentPlan == "NO_PLAN" ? true : false
                },
                {
                  content: 'Manually Assigned Products',
                  onAction: () => handleAllAction('manual'),
                  disabled: currentPlan == "NO_PLAN" ? true : false
                },
                {
                  content: 'Products Assigned by Rules',
                  onAction: () => handleAllAction('automatic'),
                  disabled: currentPlan == "NO_PLAN" ? true : false
                },
              ]}
            />
          </Popover>
        </div>,
      accessibilityLabel: 'All Collection',
      panelID: 'all-customers-content-1',
      to: "/all",
    },
    {
      id: 'prospects-1',
      content: 'History',
      panelID: 'prospects-content-1',
      to: "/",
    },
    {
      id: 'Plan-1',
      content: 'Plan',
      panelID: 'Plan-1',
      to: "/plan",
    },
  ];


  useEffect(() => {
    fetchData();
    if (selected == 2) {
      setInputField(false);
    } else {
      setInputField(true);
    }
  }, [selected])

  return (
    <>
      {/* <Dropdown setselectvalue={setselectvalue}/> */}
      <div className="container-fluid" id='container2'>
        <div className="row" id='row2'>
          <Card>
            <Tabs tabs={tabs} selected={selected} onSelect={handleTabChange}>
              {inputfield && <div className="row">
                <div className="col-md-12" id="HeadingAction">
                  <h1 id="collection">Collections</h1>
                  <div className="selectaction">
                  </div>
                </div>
              </div>}
              <div className="row">
                {inputfield ? <div className="col-md-8">
                  <Card.Section >
                    {component[selected]}
                  </Card.Section>
                </div> : <div className="col-md-12">
                  <Card.Section >
                    {component[selected]}
                  </Card.Section>
                </div>}
                {inputfield && <div className="col md-4" id="inputfield">
                  <CollectionPage />
                </div>}
              </div>
              {toastactive && <Frame><Toast content="Export File Started" onDismiss={tosttoggleActive} /></Frame>}
            </Tabs>
          </Card>
        </div>
      </div>
    </>
  );
}

export default CollectionList;