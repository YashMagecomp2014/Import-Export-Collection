import React from 'react';
import '@shopify/polaris/dist/styles.css';
import { useState, useCallback } from "react";
import '@shopify/polaris/dist/styles.css';
import { appconfig } from '../config/config';
import { GlobalAPIcall } from "../config/ApiUtils";
import { ProgressBar } from "react-bootstrap"
import HistoryList from './HistoryList';
import { Spinner } from '@shopify/polaris';
import { Toast, Frame, Page, Button } from '@shopify/polaris';
import { Banner } from '@shopify/polaris';

function CollectionPage({ fetchData }) {

  const [file, setFile] = useState("");
  const [progress, setProgress] = useState(false);
  const [active, setActive] = useState(false);
  const [validation, setValidation] = useState(false);

  const toggleActive = () => {
    setActive(false);
  }

  let handleSubmit = async (e) => {

    setActive(true);
    setValidation(false);
    e.preventDefault();
    setProgress(true);

    const data = new FormData(e.target);

    try {
      var res = await GlobalAPIcall('POST', '/file-import', data)

      setProgress(false);
      setActive(false);

      if (res.file) {
        setValidation(true);
      }
      // else if (res.title) {
      //   alert(res.title[0]);
      // }
      // else if (res.data.errors) {
      //   alert(res.data.errors[0]);
      // }

      fetchData();
      if (res.status === 200) {
        setFile("");
        setMessage("User created successfully");
        setProgress(false);

      }
      else {
        setMessage("Some error occured");
      }
    } catch (err) {
      setProgress(false);
    }
    const popupBox = () => {
      return (
        <Banner title="Order archived" onDismiss={() => { }}>
          <p>This order was archived on March 7, 2017 at 3:12pm EDT.</p>
        </Banner>
      );
    }
  };

  return (

    <div className="col-lg-4">
      <div className="row" style={{ padding: '10%' }}>
        <div className="col-md-2">
          <h1>Import</h1>
        </div>
        <div className="col-md-4"></div>
        <div className="col-md-4">
          <a href='public/Rules/Template.csv'>Download&nbsp;Template</a><br/>
          <a href='public/Rules/Rules.csv'>Rules</a>
        </div>
      </div>
      <form onSubmit={handleSubmit}>
        {/* <div id="dropzone">
          <span>Drop files here or click to select a file</span>
          <span className="hidden">Loading...</span>
          <span className="hidden"></span>
          <input className='full' id="file" name='file' value={file} type='file' accept='.csv' onChange={(e) => setFile(e.target.value)} />
        </div> */}

        <div className="formbold-mb-5 formbold-file-input">
          <input id="file" name='file' value={file} type='file' accept='.csv' onChange={(e) => setFile(e.target.value)} />
          <label htmlFor="file">
            <div>
              <span className="formbold-drop-file"> Drop files here </span>
              <span className="formbold-or"> Or </span>
              <span className="formbold-browse"> Browse </span>
            </div>
          </label>
        </div>
        {validation && <h6 style={{ color: 'red' }}>Please Insert Only .CSV file</h6>}

        <button className='btn btn-primary' id='Submitbtn'>{progress && <Spinner accessibilityLabel="Spinner example" size="small" />}Submit</button>

        {active && <Frame style={{ color: 'white' }}><Toast content="Import File Started" onDismiss={toggleActive} /></Frame>}
      </form>
    </div>

  );
}

export default CollectionPage;
