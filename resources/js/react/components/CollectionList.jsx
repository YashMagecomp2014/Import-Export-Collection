import React, { useEffect, useState, useCallback } from "react"
import CollectionPage from "./CollectionPage"
import Dropdown from "./Dropdown"
import GetAllcollection from "./GetAllcollection";
import HistoryList from './HistoryList';
import GetAutocollection from './GetAutocollection';
import GetManualcollection from './GetManualcollection';
import { Link } from "react-router-dom";
import { GlobalAPIcall } from "../config/ApiUtils"
import { Card, Tabs } from '@shopify/polaris';
import { useAppBridge } from "@shopify/app-bridge-react";
import { Fullscreen } from "@shopify/app-bridge/actions";
import { useDispatch, useSelector } from "react-redux";
import { setRedirectIndex } from "../redux/rootReducer";

function CollectionList() {
  const redirectIndex = useSelector((state) => state.redirectHistory);
  const [collections, setUsers] = useState([]);
  const [selected, setSelected] = useState(0);
  const dispatch = useDispatch();

  const fetchData = async () => {
    var res = await GlobalAPIcall('GET', '/import');
    setUsers(res)

  }
  const handleTabChange = useCallback(
    (selectedTabIndex) => setSelected(selectedTabIndex),
    [],
  );

  const app = useAppBridge();
  const fullscreen = Fullscreen.create(app);
  // Call the `ENTER` action to put the app in full-screen mode
  useEffect(() => {
    if(redirectIndex) {
      setSelected(3);
      dispatch(setRedirectIndex(false));
    }
    console.log("redirectIndex", redirectIndex);
  }, [redirectIndex]);

  const setselectvalue = () => {
    setSelected(3);
  }


  const component = [
    <GetAllcollection setselectvalue={setselectvalue} />,
    <GetManualcollection setselectvalue={setselectvalue} />,
    <GetAutocollection setselectvalue={setselectvalue} />,
    <HistoryList />,
  ]

  const tabs = [
    {
      id: 'all-customers-1',
      content: 'All',
      accessibilityLabel: 'All customers',
      panelID: 'all-customers-content-1',
      to: "/all",
    },
    {
      id: 'accepts-marketing-1',
      content: 'Manual',
      panelID: 'accepts-marketing-content-1',
      to: "/manual",
    },
    {
      id: 'repeat-customers-1',
      content: 'Automatic',
      panelID: 'repeat-customers-content-1',
      to: "/automatic",
    },
    {
      id: 'prospects-1',
      content: 'History',
      panelID: 'prospects-content-1',
      to: "/",
    },
  ];


  useEffect(() => {
    fetchData()
  }, [])

  return (
    <>
      {/* <Dropdown setselectvalue={setselectvalue}/> */}
      <div className="container-fluid" id='container2'>
        <div className="row" id='row2'>
          <Card>
            <Tabs tabs={tabs} selected={selected} onSelect={handleTabChange}>
              <Card.Section >
                {component[selected]}
              </Card.Section>
            </Tabs>
          </Card>
        </div>
      </div>
    </>
  );
}

export default CollectionList;