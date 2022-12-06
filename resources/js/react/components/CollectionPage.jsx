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
import PopUp from './PopUp';
import Swal from 'sweetalert2'
import Dropdown from './Dropdown';
import { useDispatch } from "react-redux";
import { enableLoadHistory, setRedirectIndex } from "../redux/rootReducer";
import { useAppBridge } from '@shopify/app-bridge-react';

function CollectionPage({ fetchData }) {

  const dispatch = useDispatch();
  const app = useAppBridge();
  const [file, setFile] = useState("");
  const [progress, setProgress] = useState(false);
  const [active, setActive] = useState(false);
  const [validation, setValidation] = useState(false);
  const [popup, setPopup] = useState(false);

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
      var res = await GlobalAPIcall('POST', '/file-import-1', data)

      setProgress(false);
      setActive(false);
      setFile("");

      if (res.file) {
        setValidation(true);
      }
      else if (res.title) {
        Swal.fire({
          icon: 'error',
          title: 'Error...',
          text: res.title[0],
        })
      }
      else if (res.sort_order) {
        Swal.fire({
          icon: 'error',
          title: 'Error...',
          text: res.sort_order[0],
        })
      }
      else if (res.data.errors) {
        Swal.fire({
          icon: 'error',
          title: 'Error...',
          text: res.data.errors,
        })
      }

      dispatch(enableLoadHistory());
      dispatch(setRedirectIndex(true));
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
  };

  return (
    <>
      <div className='row'>
        <div className="col-md-4">
          <a className='download' href='public/Rules/Template.csv'>Download&nbsp;Template</a><br />
        </div>
        <div className="col-md-5"></div>
        <div className="col-md-3">
          <a className='download' href='public/Rules/Rules.csv'>Rules</a>
        </div>
      </div>
      <Dropdown />
      <div className="row" id='inputDragDrop'>
        <div className="col-md-12">
          <form onSubmit={handleSubmit}>
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
            <div className="row">
              <div className="col-md-4"></div>
              <div className="col-md-8">
                {validation && <h6 style={{ color: 'red' }}>Please Insert Only .CSV file</h6>}

                <button className='btn btn-success' id='Submitbtn'>{progress && <Spinner accessibilityLabel="Spinner example" size="small" />}Import</button>

                {active && <Frame style={{ color: 'white' }}><Toast content="Import File Started" onDismiss={toggleActive} /></Frame>}
              </div>
            </div>
          </form>
        </div>
      </div>
    </>

  );
}

export default CollectionPage;
